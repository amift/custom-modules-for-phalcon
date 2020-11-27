<?php

namespace Users\Service;

use Core\Library\AbstractLibrary;
use Core\Tool\Bcrypt;
use Users\Entity\FailedLogin;
use Users\Entity\SuccessLogin;
use Users\Entity\User;
use Users\Tool\State;

class UserService extends AbstractLibrary
{

    /**
     * @var \Users\Repository\UserRepository
     */
    protected $_userRepo;

    /**
     * @var \Core\Tool\Bcrypt
     */
    protected $_bcrypt;

    /**
     * Get user object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|User
     */
    public function getById($id = null)
    {
        return $this->getUserRepo()->findObjectById($id);
    }

    /**
     * Get user object by Email value.
     * 
     * @access public
     * @param string $email
     * @return null|User
     */
    public function getByEmail($email = null)
    {
        return $this->getUserRepo()->findObjectByEmail($email);
    }

    /**
     * Verify if entered pass is same as hashed password
     * 
     * @access public
     * @param string $plain Plain password (from input)
     * @param string $hash Hash (stored value from DB etc)
     * @return boolean
     */
    public function verifyPassword($plain, $hash)
    {
        return $this->getBcrypt()->verify($plain, $hash);
    }

    /**
     * Register success login data.
     * 
     * @access public
     * @param User $user
     * @return void
     */
    public function registerSuccessLogin(User $user)
    {
        $successLogin = new SuccessLogin();
        $successLogin->setIpAddress($this->request->getClientAddress());
        $successLogin->setUserAgent($this->request->getUserAgent());
        $successLogin->setSessionId($this->session->getId());
        $successLogin->setUser($user);

        $user->setLoginLastIp($successLogin->getIpAddress());
        $user->setLoginLastAt($successLogin->getDate());
        $user->setLoginFailCount(0);

        $this->getEntityManager()->persist($successLogin);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Register failed login data.
     * Afer 5 invalid attemps will block user.
     * 
     * @access public
     * @param User $user
     * @param string $email
     * @return void
     */
    public function registerFailedLogin(User $user = null, $email = null)
    {
        $failedLogin = new FailedLogin();
        $failedLogin->setIpAddress($this->request->getClientAddress());
        $failedLogin->setUserAgent($this->request->getUserAgent());
        $failedLogin->setSessionId($this->session->getId());

        if (is_object($user)) {
            $failedLogin->setUserEmail($user->getEmail());
            $failedLogin->setUser($user);
            $user->setLoginFailCount($user->getLoginFailCount()+1);
            if ($user->getLoginFailCount() > 4) {
                $user->setState(State::STATE_BLOCKED);
            }
            $this->getEntityManager()->persist($user);
        } else {
            $failedLogin->setUserEmail($email);
        }

        $this->getEntityManager()->persist($failedLogin);
        $this->getEntityManager()->flush();
    }

    /**
     * Get User entity repository
     * 
     * @access public
     * @return \Users\Repository\UserRepository
     */
    public function getUserRepo()
    {
        if ($this->_userRepo === null || !$this->_userRepo) {
            $this->_userRepo = $this->getEntityRepository(User::class);
        }

        return $this->_userRepo;
    }

    /**
     * Get Bcrypt
     * 
     * @access protected
     * @return \Core\Tool\Bcrypt
     */
    protected function getBcrypt()
    {
        if ($this->_bcrypt === null || !$this->_bcrypt) {
            $this->_bcrypt = new Bcrypt();
        }

        return $this->_bcrypt;
    }

    /**
     * Save new user data into DB.
     * 
     * @access public
     * @param array $data
     * @return User
     */
    public function createUser($data = [])
    {
        // Crypt password
        $hash = $this->getBcrypt()->create($data['newPassword']);

        // Set user data
        $user = new User();
        $user->exchangeArray($data);
        $user->setPassword($hash);

        // Save data
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * Update existing user general data into DB.
     * 
     * @access public
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateUser(User $user, $data = [])
    {
        // Set user data
        $user->exchangeArray($data);

        // Save data
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * Update existing user password data into DB.
     * 
     * @access public
     * @param User $user
     * @param array $data
     * @return User
     */
    public function changePassword(User $user, $data = [])
    {
        // Crypt password
        $hash = $this->getBcrypt()->create($data['newPassword']);

        // Set user data
        $user->setPassword($hash);

        // Save data
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

}
