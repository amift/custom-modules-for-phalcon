<?php

namespace Members\Controller\Backend;

use Bookings\Entity\Booking;
use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Members\Entity\Withdraws;
use Members\Forms\WithdrawEditForm;

class WithdrawsController extends AbstractBackendController
{

    /**
     * @var \Members\Repository\WithdrawsRepository
     */
    protected $_withdrawsRepo;

    /**
     * Withdraw list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->withdraws;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getWithdrawsRepo()->createQueryBuilder('w')
                ->select('w, m')
                ->leftJoin('w.member', 'm')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('w.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('state', Filters::TYPE_TEXT, 'w.state')
            ->addField('type', Filters::TYPE_TEXT, 'w.type')
            ->searchInFields('search', [ 
                'm.email', 'm.username',
            ])
        ;

        $filters->apply($qb, 'm');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Withdraw overview info view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function viewAction()
    {
        $id       = $this->dispatcher->getParam('id');
        $withdraw = $this->getWithdrawsRepo()->findObjectById($id);
        $tab      = 'general';

        if (null === $withdraw) {
            $this->flashSession->error(sprintf('Withdraws with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'withdraws_list']));
        }

        $this->view->setVars(compact('withdraw', 'tab'));
    }

    /**
     * Withdraw edit view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function editAction()
    {
        $id       = $this->dispatcher->getParam('id');
        $withdraw = $this->getWithdrawsRepo()->findObjectById($id);
        $tab      = 'edit';

        if (null === $withdraw) {
            $this->flashSession->error(sprintf('Withdraws with ID value "%s" not found', $id));
            return $this->response->redirect($this->url->get(['for' => 'withdraws_list']));
        }

        $form = new WithdrawEditForm($withdraw, ['edit' => true]);
        $action = $this->url->get(['for' => 'withdraws_edit', 'id' => $withdraw->getId()]);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $withdraw);

                // If rejected
                if ($withdraw->isRejected() || $withdraw->isTransferMoney() || $withdraw->isPending()) {
                    $withdraw->unsetTransactionNumber();
                    $withdraw->unsetTransactionDate();
                }

                // If transfer money
                if ($withdraw->isTransferMoney() || $withdraw->isPending()) {
                    $withdraw->unsetReason();
                }

                // If completed
                if ($withdraw->isCompleted()) {
                    $d = \DateTime::createFromFormat('d/m/Y H:i:s', $withdraw->getTransactionDate());
                    if (!is_object($d)) {
                        $d = new \DateTime('now');
                    }
                    $withdraw->setTransactionDate($d);
                    $withdraw->unsetReason();
                    
                    // Add booking
                    $booking = new Booking();
                    $booking->setTypeAsExpenses();
                    $booking->setActionAsExpensesWithdraw();
                    $booking->setDate($d);
                    $booking->setAmount($withdraw->getAmount());
                    $booking->setCurrency($withdraw->getCurrency());
                    $booking->setComment(sprintf('Member money witdraw for %s pts', $withdraw->getPts()));
                    $this->getEntityManager()->persist($booking);

                    // Update member points
                    $member = $withdraw->getMember();
                    if (is_object($member)) {
                        $booking->setComment(sprintf('Member [%s, id: %s] money witdraw for %s pts', (string)$member, $member->getId(), $withdraw->getPts()));
                        $memberPoints = $member->getTotalPointsData();
                        $currentWithdrawedPts = $memberPoints->getTotalWithdrawed();
                        $memberPoints->setTotalWithdrawed($currentWithdrawedPts + (int)$withdraw->getPts());
                        $memberPoints->recalculateActual();
                    }
                }

                $this->getEntityManager()->flush();

                // Back to list
                $this->flashSession->success(sprintf('Witdraw "%s" info updated successfully!', (string)$withdraw));
                return $this->response->redirect($this->url->get(['for' => 'withdraws_list']));
            }
        }

        $this->view->setVars(compact('withdraw', 'tab', 'form', 'action', 'error'));
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
