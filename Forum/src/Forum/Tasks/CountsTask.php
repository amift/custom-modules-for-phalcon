<?php

namespace Forum\Tasks;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;
use Forum\Entity\ForumCategory;
use Forum\Entity\ForumReply;
use Forum\Entity\ForumTopic;
use System\Entity\CronJob;

class CountsTask extends \Phalcon\Cli\Task
{

    /**
     * @var \Forum\Repository\ForumCategoryRepository
     */
    protected $_categoriesRepo;

    /**
     * @var \Forum\Repository\ForumReplyRepository
     */
    protected $_repliesRepo;

    /**
     * @var \Forum\Repository\ForumTopicRepository
     */
    protected $_topicsRepo;

    /**
     * @var string
     */
    protected $_stackTrace = '';

    public function updateAllAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'forum counts updateAll';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {

            $this->_stackTrace.= sprintf('Start TOPICS') . PHP_EOL;
            $this->updateTopicsAction();

            $this->_stackTrace.= sprintf('PAUSE FOR 2 SECONDS') . PHP_EOL;
            sleep(2);

            $this->_stackTrace.= sprintf('Start CATEGORIES') . PHP_EOL;
            $this->updateCategoriesAction();

        } catch (ORMInvalidArgumentException $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        } catch (\Exception $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        }

        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->stopRunning($this->_stackTrace, $success, $errorMsg);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Task for categories topics and comments count update
     * 
     * @access public
     * @throws \Core\Exception\RuntimeException
     */
    public function updateCategoriesAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'forum counts updateCategories';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {

            $categoryRepo = $this->getForumCategoryRepo();
            $topicRepo = $this->getForumTopicRepo();
            $total = 0;
            $batchSize = 200;

            $rows = $categoryRepo->getAllCategoriesFromLowestLevelQuery()->iterate();
            foreach ($rows as $row) {
                $category = $row[0];
                /* @var $category ForumCategory */

                $this->_stackTrace.= sprintf('Processing category nr. %d [id: %d]', ++$total, $category->getId()) . PHP_EOL;

                /*$latestReplyOnTopic = null;
                $topicsIds = [];
                $topics = $topicRepo->getVisibleTopicsByCategory($category);
                foreach ($topics as $topic) {
                    $topicsIds[] = $topic->getId();
                    $latestReplyOnTopic = $topic;
                }
                $category->setTopicsCount(count($topicsIds));*/

                $topicsCount = $topicRepo->getVisibleTotalCountByCategory($category);
                if ($topicsCount === null) {
                    $topicsCount = 0;
                }
                $category->setTopicsCount($topicsCount);

                $repliesCount = $topicRepo->getVisibleTotalRepliesSumCountByCategory($category);
                if ($repliesCount === null) {
                    $repliesCount = 0;
                }
                $category->setCommentsCount($repliesCount);

                $latestReplyOnTopic = $topicRepo->getLastReplyOnTopicByCategory($category);
                if (is_object($latestReplyOnTopic)) {
                    $category->setLastReplyOnTopic($latestReplyOnTopic);
                    $member = $latestReplyOnTopic->getLastReplyBy();
                    if (is_object($member)) {
                        $category->setLastReplyBy($member);
                        $category->setLastReplyByUsername((string)$member);
                    }
                    $date = $latestReplyOnTopic->getLastReplyAt();
                    if (is_object($date)) {
                        $category->setLastReplyAt($date);
                    }
                    $category->setLastReply($latestReplyOnTopic->getLastReply());
                }

                if (($total % $batchSize) === 0) {
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->clear();
                    sleep(1);
                }
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            $this->_stackTrace.= sprintf('Total categories processed: %d', $total) . PHP_EOL;

        } catch (ORMInvalidArgumentException $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        } catch (\Exception $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        }

        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->stopRunning($this->_stackTrace, $success, $errorMsg);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Task for topics replies count update
     * 
     * @access public
     * @throws \Core\Exception\RuntimeException
     */
    public function updateTopicsAction()
    {
        if (!isConsoleRequest()) {
            throw new \Core\Exception\RuntimeException('Invalid access. Must be CLI request');
        }

        $cronAction = 'forum counts updateTopics';
        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->startRunning();
            $this->getEntityManager()->flush($cronjob);
        }

        $this->_stackTrace = '';
        $success = true;
        $errorMsg = '';

        try {

            $replyRepo = $this->getForumReplyRepo();
            $topicRepo = $this->getForumTopicRepo();
            $total = 0;
            $batchSize = 200;

            $rows = $topicRepo->getAllVisibleTopicsQuery()->iterate();
            foreach ($rows as $row) {
                $topic = $row[0];
                /* @var $topic ForumTopic */

                $this->_stackTrace.= sprintf('Processing topic nr. %d [id: %d]', ++$total, $topic->getId()) . PHP_EOL;

                $repliesCount = $replyRepo->getVisibleTotalCountByTopic($topic->getId());
                if ($repliesCount === null) {
                    $repliesCount = 0;
                }
                $topic->setCommentsCount($repliesCount);

                $latestReply = $replyRepo->getLastReplyByTopic($topic->getId());
                if (is_object($latestReply)) {
                    $member = $latestReply->getMember();
                    if (is_object($member)) {
                        $topic->setLastReplyBy($member);
                        $topic->setLastReplyByUsername((string)$member);
                    }
                    $date = $latestReply->getCreatedAt();
                    if (is_object($date)) {
                        $topic->setLastReplyAt($date);
                    }
                    $topic->setLastReply($latestReply->getContent());
                }

                if (($total % $batchSize) === 0) {
                    $this->getEntityManager()->flush();
                    $this->getEntityManager()->clear();
                    sleep(1);
                }
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            $this->_stackTrace.= sprintf('Total topics processed: %d', $total) . PHP_EOL;

        } catch (ORMInvalidArgumentException $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        } catch (\Exception $exc) {
            $success = false;
            $errorMsg = (string)$exc;
        }

        $cronjob = $this->getEntityRepository(CronJob::class)->findObjectByCronAction($cronAction);
        if (is_object($cronjob)) {
            $cronjob->stopRunning($this->_stackTrace, $success, $errorMsg);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get EntityManager instance
     * 
     * @access protected
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository Repository class
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager()->getRepository($entityName);
    }

    /**
     * @return \Forum\Repository\ForumCategoryRepository
     */
    protected function getForumCategoryRepo()
    {
        if ($this->_categoriesRepo === null || !$this->_categoriesRepo) {
            $this->_categoriesRepo = $this->getEntityRepository(ForumCategory::class);
        }

        return $this->_categoriesRepo;
    }

    /**
     * @return \Forum\Repository\ForumReplyRepository
     */
    protected function getForumReplyRepo()
    {
        if ($this->_repliesRepo === null || !$this->_repliesRepo) {
            $this->_repliesRepo = $this->getEntityRepository(ForumReply::class);
        }

        return $this->_repliesRepo;
    }

    /**
     * @return \Forum\Repository\ForumTopicRepository
     */
    protected function getForumTopicRepo()
    {
        if ($this->_topicsRepo === null || !$this->_topicsRepo) {
            $this->_topicsRepo = $this->getEntityRepository(ForumTopic::class);
        }

        return $this->_topicsRepo;
    }

}
