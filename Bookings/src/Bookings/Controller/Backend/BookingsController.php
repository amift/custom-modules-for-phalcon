<?php

namespace Bookings\Controller\Backend;

use Common\Controller\AbstractBackendController;
use Common\Tool\Filters;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Bookings\Entity\Booking;
use Bookings\Forms\BookingForm;

class BookingsController extends AbstractBackendController
{

    /**
     * @var \Bookings\Repository\BookingRepository
     */
    protected $_bookingsRepo;

    /**
     * Bookings list view.
     *
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function indexAction()
    {
        $perPage = $this->config->settings->page_size->bookings;
        $currentPage = $this->request->getQuery('page', 'int', 1);

        $qb = $this->getBookingRepo()->createQueryBuilder('b')
                ->select('b')
                ->setFirstResult(($currentPage - 1) * $perPage)
                ->setMaxResults($perPage)
                ->orderBy('b.id', 'DESC');

        $filters = new Filters($this->request);
        $filters
            ->addField('type')
            ->addField('action')
            ->addField('dateFrom', Filters::TYPE_DATE, 'b.date', Filters::COMP_GTE)
            ->addField('dateTo', Filters::TYPE_DATE, 'b.date', Filters::COMP_LTE)
            ->searchInFields('search', [ 
                'b.comment',
            ])
        ;

        $filters->apply($qb, 'b');
        $paginator = new Paginator($qb->getQuery(), true);

        $this->view->setVars(compact('paginator', 'perPage', 'currentPage', 'filters'));
    }

    /**
     * Booking add view.
     * 
     * @access public
     * @return \Phalcon\Mvc\View
     */
    public function addAction()
    {
        $booking = new Booking();
        $form = new BookingForm($booking, ['edit' => true]);
        $action = $this->url->get(['for' => 'bookings_add']);
        $error  = '';

        if ($this->request->isPost()) {
            if ($form->isValid($this->request->getPost())) {
                $form->bind($this->request->getPost(), $booking);

                $d = \DateTime::createFromFormat('d/m/Y H:i:s', $booking->getDate());
                $booking->setDate($d);

                // Save data
                $this->getEntityManager()->persist($booking);
                $this->getEntityManager()->flush();

                // Back to list
                $this->flashSession->success(sprintf('Booking "%s" added successfully!', (string)$booking));
                return $this->response->redirect($this->url->get(['for' => 'bookings_list']));
            }
        }

        $this->view->setVars(compact('booking', 'form', 'action', 'error'));
    }

    /**
     * Get Booking entity repository
     * 
     * @access public
     * @return \Bookings\Repository\BookingRepository
     */
    public function getBookingRepo()
    {
        if ($this->_bookingsRepo === null || !$this->_bookingsRepo) {
            $this->_bookingsRepo = $this->getEntityRepository(Booking::class);
        }

        return $this->_bookingsRepo;
    }

}
