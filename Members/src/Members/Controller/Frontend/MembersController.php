<?php

namespace Members\Controller\Frontend;

use Articles\Entity\Article;
use Communication\Tool\TemplateCode;
use Common\Controller\AbstractFrontendController;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Members\Entity\Member;
use Members\Entity\TemporaryPassword;
use Members\Entity\Withdraws;
use Members\Forms\ChangePasswordForm;
use Members\Forms\LoginForm;
use Members\Forms\PwdReminderForm;
use Members\Forms\RegisterForm;
use Members\Forms\WithdrawCreateForm;

class MembersController extends AbstractFrontendController
{

    /**
     * @var \Articles\Repository\ArticleRepository
     */
    protected $_articleRepo;

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_membersRepo;

    /**
     * @var \Members\Repository\WithdrawsRepository
     */
    protected $_withdrawsRepo;

    /**
     * Member login action
     *
     * @access public
     * @return void
     */
    public function loginAction()
    {
        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $form    = new LoginForm();
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    try {
                        $this->auth->check([
                            'email'     => $this->request->getPost('login_email', 'email', ''),
                            'password'  => $this->request->getPost('login_password', 'string', '')
                        ]);
                        $success = true;
                    } catch (\Exception $exc) {
                        $errors['login_email'] = $exc->getMessage();
                    }
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }
                }

                $this->response->setJsonContent(['success' => $success, 'errors' => $errors]);
                return $this->response->send();
            }
        }

        return $this->response->redirect('');
    }

    /**
     * Member logout action
     *
     * @access public
     * @return void
     */
    public function logoutAction()
    {
        $this->auth->remove();

        if ($this->request->isAjax() == true) {
            $this->response->setJsonContent(['success' => true]);
            return $this->response->send();
        }

        return $this->response->redirect('');
    }

    /**
     * Member register action
     *
     * @access public
     * @return void
     */
    public function registerAction()
    {
        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $form    = new RegisterForm();
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    try {

                        $member = $this->di->get('memberService')->registerNewMember($this->request->getPost());
                        if (is_object($member)) {
                            $success = true;

                            // Set notification parameters
                            $notifParams = [
                                'username' => $member->getUsername(),
                                'url_activation' => $this->config->web_url . $this->url->get(['for' => 'member_activation', 'code' => $member->getConfirmCode()]),
                            ];

                            // Schedule email notification
                            $this->notification_scheduler->scheduleNotification(
                                $member->getId(),
                                TemplateCode::CODE_MEMBER_REGISTRATION, 
                                $notifParams
                            );
                        } else {
                            $errors['username'] = 'Kaut kas nogāja greizi. Lūdzu, mēģini vēlreiz!';
                        }

                    } catch (\Exception $exc) {
                        $errors['username'] = $exc->getMessage();
                    }

                    $response = ['success' => $success, 'errors' => $errors, 'redirect' => $this->url->get(['for' => 'member_registered_success'])];
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }

                    $response = ['success' => $success, 'errors' => $errors];
                }

                $this->response->setJsonContent($response);
                return $this->response->send();
            }
        }

        return $this->response->redirect('');
    }

    /**
     * Member registered success info view
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function registeredSuccessAction()
    {
        if ($this->auth->isAuthorised()) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'invalidAccess',
            ]);
            return false;
        }

        $contentClass = 'my-profile';
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass'));
    }

    /**
     * Member confirmation action
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function accountActivationAction()
    {
        $invalidAccess = true;

        // Get code from url
        $code = $this->dispatcher->getParam('code', 'string', '');

        // If not logged in search for member 
        if (!$this->auth->isAuthorised()) {
            $member = $this->getMemberRepo()->findObjectByConfirmCode($code);
            if (is_object($member)) {

                // If not yet confirmed
                if ($member->isConfirmed() === false) {
                    $member->setConfirmed(true);
                    $this->getEntityManager()->flush($member);
                    $invalidAccess = false;

                    // Set as logged in
                    $this->auth->setMemberAsLogedIn($member->getId());
                }
            }
        }

        if ($invalidAccess) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'invalidAccess',
            ]);
            return false;
        }

        $contentClass = 'my-profile';
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass', 'member'));
    }

    /**
     * Reset password action
     *
     * @access public
     * @return void
     */
    public function resetPasswordAction()
    {
        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $form    = new PwdReminderForm();
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    try {
                        $email = $this->request->getPost('reminder_email', 'email', '');
                        $member = $this->getMemberRepo()->findObjectByEmail($email);
                        if (is_object($member)) {

                            $tmpPassword = \Core\Tool\Rand::getString(11);

                            // Save new temporary password
                            $newPassword = new TemporaryPassword();
                            $newPassword->setMember($member);
                            $newPassword->setPassword($this->di->get('memberService')->createPasswordHash($tmpPassword));
                            $newPassword->setCreatedFromIp($this->request->getClientAddress());
                            $newPassword->setUserAgent($this->request->getUserAgent());
                            $newPassword->setSessionId($this->session->getId());
                            $this->getEntityManager()->persist($newPassword);
                            $this->getEntityManager()->flush($newPassword);

                            // Set notification parameters
                            $notifParams = [
                                'username' => $member->getUsername(),
                                'tmp_password' => $tmpPassword,
                                'url_login' => $this->config->web_url,
                            ];

                            // Schedule email notification
                            $this->notification_scheduler->scheduleNotification(
                                $member->getId(),
                                TemplateCode::CODE_MEMBER_PASSWORD_RECOVERY, 
                                $notifParams
                            );

                            $success = true;
                        } else {
                            $errors['reminder_email'] = 'E-pasta adrese nav precīza';
                        }
                    } catch (\Exception $exc) {
                        $error = $exc->getMessage();
                        $errors['reminder_email'] = $exc->getMessage();
                    }

                    $response = ['success' => $success, 'errors' => $errors, 'redirect' => $this->url->get(['for' => 'member_reset_password_success'])];
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }

                    $response = ['success' => $success, 'errors' => $errors];
                }

                $this->response->setJsonContent($response);
                return $this->response->send();
            }
        }

        return $this->response->redirect('');
    }

    /**
     * Member reset password success info view
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function resetPasswordSuccessAction()
    {
        if ($this->auth->isAuthorised()) {
            $this->dispatcher->forward([
                'module'        => 'Application',
                'namespace'     => 'Application\Controller\Frontend',
                'controller'    => 'error',
                'action'        => 'invalidAccess',
            ]);
            return false;
        }
        
        $contentClass = 'my-profile';
        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact('contentClass'));
    }

    /**
     * Change password action
     *
     * @access public
     * @return void
     */
    public function changePasswordAction()
    {
        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {

                $member = $this->auth->getAuthorisedUser();
                /* @var $member Member */

                $form    = new ChangePasswordForm($member, []);
                $success = false;
                $errors  = [];

                if ($form->isValid($this->request->getPost())) {
                    try {

                        $enteredCurrentPwd = $this->request->getPost('old_password', 'string', '');

                        $isValid = true;

                        // Check current and actual temporary passwords
                        if (!$this->di->get('memberService')->verifyPassword($enteredCurrentPwd, $member->getPassword())) {
                            $isValid = false;
                            $tmpPasswords = $this->di->get('memberService')->getActualTemporaryPasswords($member->getId());
                            if (count($tmpPasswords) > 0) {
                                foreach ($tmpPasswords as $tmp) {
                                    if ($this->di->get('memberService')->verifyPassword($enteredCurrentPwd, $tmp['password'])) {
                                        $isValid = true;
                                        break;
                                    }
                                }
                            }
                        }

                        // If actual and temporary password is not valid otherwise save new password
                        if (!$isValid) {
                            $errors['old_password'] = 'Nepareiza tekošā parole!';
                        } else {
                            if ($this->request->getPost('old_password', 'string', '') == $this->request->getPost('new_password', 'string', '')) {
                                $errors['new_password'] = 'Jaunā parole nevar būt identiska tekošajai!';
                            } else {
                                $member = $this->di->get('memberService')->changePassword($member, $this->request->getPost());
                                if (is_object($member)) {
                                    $success = true;
                                } else {
                                    $errors['old_password'] = 'Kaut kas nogāja greizi. Lūdzu, mēģini vēlreiz!';
                                }
                            }
                        }

                    } catch (\Exception $exc) {
                        $errors['old_password'] = $exc->getMessage();
                    }
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }
                }

                $this->response->setJsonContent(['success' => $success, 'errors' => $errors]);
                return $this->response->send();
            }
        }

        return $this->response->redirect('');
    }

    /**
     * Member profile view
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function profileAction()
    {
        $member = $this->auth->getAuthorisedUser();
        /* @var $member Member */

        $contentClass = 'my-profile';

        $fullUrl    = $this->url->get(['for' => 'member_profile']);
        $page       = (int)$this->request->getQuery('page', 'int', 1);
        $perPage    = 10;
        $paginator  = new Paginator(
            $this->getArticleRepo()->getMemberArticlesQuery(
                $member->getId(), $page, $perPage
            ), 
            true
        );

        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact(
            'contentClass', 'member', 'page', 'perPage', 'paginator', 'fullUrl'
        ));
    }

    /**
     * Member earnings view
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function earningsAction()
    {
        $member = $this->getMemberRepo()->findObjectById($this->auth->getAuthorisedUserId());
        /* @var $member Member */

        $minPts = 1000;
        $maxPts = is_object($member->getTotalPointsData()) ? (int)$member->getTotalPointsData()->getTotalActual() : 0;
        $contentClass = 'my-profile';
        $withdraw = new Withdraws();
        $form = new WithdrawCreateForm($withdraw, []);
        $action = $fullUrl = $this->url->get(['for' => 'member_earnings']);

        // New withdraw request processing
        if ($this->request->isPost()) {
            if ($this->request->isAjax() == true) {
                $success = false;
                $errors  = [];
                if ($form->isValid($this->request->getPost())) {
                    $form->bind($this->request->getPost(), $withdraw);

                    $doSave = true;

                    // Custom validations
                    $pts = (int)$withdraw->getPts();
                    if ($pts < $minPts) {
                        $errors['pts'] = sprintf('Vismaz %s punkti jānorāda apmaiņai', $minPts);
                        $doSave = false;
                    } elseif ($pts > $maxPts) {
                        $errors['pts'] = sprintf('Jums pieejami max %s punkti apmaiņai', $maxPts);
                        $doSave = false;
                    }
                    
                    // Check if member has pending request
                    if ($this->getWithdrawsRepo()->memberHasPendingRequest($member->getId())) {
                        $errors['pts'] = sprintf('Jums jau ir neapstrādāts pieteikums. Jaunu pieteikt var kad iepriekšējie pieteikumi ir apstrādāti.');
                        $doSave = false;
                    }
                    
                    // Calculate amount
                    $oneEurPts = 200;
                    $amount = \Common\Tool\NumberTool::div($pts, $oneEurPts);
                    if ((int)$amount < 0) {
                        $errors['pts'] = sprintf('Sistēmas kļūda punktu konvertācijā. Lūdzam mēģinēt vēlāk vai uzrakstīt lapas administrācijai');
                        $doSave = false;
                    }
                    
                    // 
                    if ($doSave) {
                        $withdraw->setAmount($amount);
                        $withdraw->setMember($member);
                        $withdraw->setCreatedFromIp($this->request->getClientAddress());
                        $withdraw->setUserAgent($this->request->getUserAgent());
                        $withdraw->setSessionId($this->session->getId());
                        $this->getEntityManager()->persist($withdraw);
                        $this->getEntityManager()->flush();

                        $success = true;
                    }

                    $response = ['success' => $success, 'errors' => $errors, 'redirect' => $this->url->get(['for' => 'member_earnings'])];
                } else {
                    foreach ($form->getElements() as $element) {
                        $name = $element->getName();
                        $messages = $form->getFieldErrorMessages($name);
                        $errors[$name] = $messages !== null ? $messages[0] : '';
                    }

                    $response = ['success' => $success, 'errors' => $errors];
                }

                $this->response->setJsonContent($response);
                return $this->response->send();
            }
        }

        // Content output
        $perPage   = 10;
        $page      = (int)$this->request->getQuery('page', 'int', 1);
        $paginator = new Paginator(
            $this->getWithdrawsRepo()->getMemberWithdrawsQuery(
                $member->getId(), $page, $perPage
            ), 
            true
        );

        $this->metaData->setRobots(['index' => false]);
        $this->view->setVars(compact(
            'form', 'action', 'contentClass', 'member', 
            'page', 'perPage', 'paginator', 'fullUrl'
        ));
    }

    /**
     * Get Article entity repository
     * 
     * @access public
     * @return \Articles\Repository\ArticleRepository
     */
    public function getArticleRepo()
    {
        if ($this->_articleRepo === null || !$this->_articleRepo) {
            $this->_articleRepo = $this->getEntityRepository(Article::class);
        }

        return $this->_articleRepo;
    }

    /**
     * Get Member entity repository
     * 
     * @access public
     * @return \Members\Repository\MemberRepository
     */
    public function getMemberRepo()
    {
        if ($this->_membersRepo === null || !$this->_membersRepo) {
            $this->_membersRepo = $this->getEntityRepository(Member::class);
        }

        return $this->_membersRepo;
    }

    /**
     * Get Withdraws entity repository
     * 
     * @access public
     * @return \Members\Repository\WithdrawsRepository
     */
    public function getWithdrawsRepo()
    {
        if ($this->_withdrawsRepo === null || !$this->_withdrawsRepo) {
            $this->_withdrawsRepo = $this->getEntityRepository(Withdraws::class);
        }

        return $this->_withdrawsRepo;
    }

}
