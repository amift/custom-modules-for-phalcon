<?php

namespace Members\View\Helper;

use Phalcon\Mvc\User\Component;
use Phalcon\Mvc\View\Simple as SimpleView;
use Members\Forms\ChangePasswordForm;
use Members\Forms\LoginForm;
use Members\Forms\PwdReminderForm;
use Members\Forms\RegisterForm;

class PopupFormsHelper extends Component
{

    /**
     * Get SimpleView object for view rendering.
     * 
     * @access protected
     * @return SimpleView
     */
    protected function getView()
    {
        $view = new SimpleView();
        $view->setViewsDir(ROOT_PATH . str_replace('/', DS, '/module/Members/view/' . APP_TYPE . '/members/'));

        return $view;
    }

    public function renderLogin()
    {
        $form   = new LoginForm();
        $action = $this->url->get(['for' => 'member_login']);

        return $this->getView()->render(
            'popups/login', 
            compact('form', 'action')
        );
    }

    public function renderRegister()
    {
        $form   = new RegisterForm();
        $action = $this->url->get(['for' => 'member_register']);

        return $this->getView()->render(
            'popups/register', 
            compact('form', 'action')
        );
    }

    public function renderPwdReminder()
    {
        $form   = new PwdReminderForm();
        $action = $this->url->get(['for' => 'member_reset_password']);

        return $this->getView()->render(
            'popups/pwdReminder', 
            compact('form', 'action')
        );
    }

    public function renderChangePassword()
    {
        $member = $this->auth->getAuthorisedUser();
        /* @var $member Member */

        $form   = new ChangePasswordForm($member, []);
        $action = $this->url->get(['for' => 'member_change_password']);

        return $this->getView()->render(
            'popups/changePassword', 
            compact('form', 'action')
        );
    }

}
