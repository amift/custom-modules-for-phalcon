<?php

namespace Members\Service;

use Core\Library\AbstractLibrary;
use Core\Tool\Bcrypt;
use Members\Entity\FailedLogin;
use Members\Entity\SuccessLogin;
use Members\Entity\Member;
use Members\Entity\TemporaryPassword;
use Members\Entity\TotalPoints;
use Members\Tool\State;

class MemberService extends AbstractLibrary
{

    /**
     * @var \Members\Repository\MemberRepository
     */
    protected $_memberRepo;

    /**
     * @var \Members\Repository\TemporaryPasswordRepository
     */
    protected $_tmpPasswordRepo;

    /**
     * @var \Core\Tool\Bcrypt
     */
    protected $_bcrypt;

    /**
     * Get member object by ID value.
     * 
     * @access public
     * @param int $id
     * @return null|Member
     */
    public function getById($id = null)
    {
        return $this->getMemberRepo()->findObjectById($id);
    }

    /**
     * Get member object by Email value.
     * 
     * @access public
     * @param string $email
     * @return null|Member
     */
    public function getByEmail($email = null)
    {
        return $this->getMemberRepo()->findObjectByEmail($email);
    }

    /**
     * Get member actual temporary passwords
     * 
     * @access public
     * @param int $memberId
     * @param int $minutes
     * @return array
     */
    public function getActualTemporaryPasswords($memberId, $minutes = 60)
    {
        return $this->getTemporaryPasswordRepo()->getActualTemporaryPasswords($memberId, $minutes);
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
     * Create password hash from plain text
     * 
     * @access public
     * @param string $plain
     * @return string
     */
    public function createPasswordHash($plain)
    {
        return $this->getBcrypt()->create($plain);
    }

    /**
     * Get TemporaryPassword entity repository
     * 
     * @access public
     * @return \Members\Repository\TemporaryPasswordRepository
     */
    public function getTemporaryPasswordRepo()
    {
        if ($this->_tmpPasswordRepo === null || !$this->_tmpPasswordRepo) {
            $this->_tmpPasswordRepo = $this->getEntityRepository(TemporaryPassword::class);
        }

        return $this->_tmpPasswordRepo;
    }

    /**
     * Get Member entity repository
     * 
     * @access public
     * @return \Members\Repository\MemberRepository
     */
    public function getMemberRepo()
    {
        if ($this->_memberRepo === null || !$this->_memberRepo) {
            $this->_memberRepo = $this->getEntityRepository(Member::class);
        }

        return $this->_memberRepo;
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
     * Save new member data into DB.
     * 
     * @access public
     * @param array $data
     * @return Member
     */
    public function registerNewMember($data = [])
    {
        // Crypt password
        $hash = $this->createPasswordHash($data['password']);

        // Set member data
        $member = new Member();
        $member->setUsername($data['username']);
        $member->setEmail($data['email']);
        $member->setPassword($hash);
        $member->setCreatedFromIp($this->request->getClientAddress());
        $member->setState(State::STATE_ACTIVE);
        $member->setConfirmed(false);

        // Save data
        $this->getEntityManager()->persist($member);
        $this->getEntityManager()->flush();

        // Set default total points values
        $totals = new TotalPoints();
        $totals->setMember($member);
        $this->getEntityManager()->persist($totals);
        $this->getEntityManager()->flush();

        return $member;
    }

    /**
     * Register success login data.
     * 
     * @access public
     * @param Member $member
     * @return void
     */
    public function registerSuccessLogin(Member $member)
    {
        $successLogin = new SuccessLogin();
        $successLogin->setIpAddress($this->request->getClientAddress());
        $successLogin->setUserAgent($this->request->getUserAgent());
        $successLogin->setSessionId($this->session->getId());
        $successLogin->setMember($member);

        $member->setLoginLastIp($successLogin->getIpAddress());
        $member->setLoginLastAt($successLogin->getDate());

        $this->getEntityManager()->persist($successLogin);
        $this->getEntityManager()->persist($member);
        $this->getEntityManager()->flush();
    }

    /**
     * Register failed login data.
     * 
     * @access public
     * @param Member $member
     * @param string $email
     * @return void
     */
    public function registerFailedLogin(Member $member = null, $email = null)
    {
        $failedLogin = new FailedLogin();
        $failedLogin->setIpAddress($this->request->getClientAddress());
        $failedLogin->setUserAgent($this->request->getUserAgent());
        $failedLogin->setSessionId($this->session->getId());

        if (is_object($member)) {
            $failedLogin->setUserEmail($member->getEmail());
            $failedLogin->setMember($member);
            $this->getEntityManager()->persist($member);
        } else {
            $failedLogin->setUserEmail($email);
        }

        $this->getEntityManager()->persist($failedLogin);
        $this->getEntityManager()->flush();
    }

    /**
     * Update existing member general data into DB.
     * 
     * @access public
     * @param Member $member
     * @param array $data
     * @return User
     */
    public function updateMember(Member $member, $data = [])
    {
        // Set user data
        $member->exchangeArray($data);

        // Save data
        $this->getEntityManager()->persist($member);
        $this->getEntityManager()->flush();

        return $member;
    }

    /**
     * Update existing member password data into DB.
     * 
     * @access public
     * @param Member $member
     * @param array $data
     * @return User
     */
    public function changePassword(Member $member, $data = [])
    {
        // Crypt password
        $hash = $this->createPasswordHash($data['new_password']);

        // Set member data
        $member->setPassword($hash);

        // Save data
        $this->getEntityManager()->persist($member);
        $this->getEntityManager()->flush();

        return $member;
    }

    public function getNewsletterReceiversByCriterias($criterias = [])
    {
        return $this->getMemberRepo()->getNewsletterReceiversByCriterias($criterias);
    }
}
