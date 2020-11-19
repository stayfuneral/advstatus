<?php


namespace Adv;

use CommonDBTM;
use DB;
use ITILFollowup;
use Ticket as GlpiTicket;
use Ticket_User;


class Ticket extends CommonDBTM
{
    const TICKET_STATUS_IN_WORK = 2;
    const TICKET_STATUS_PENDING = 4;
    const TICKET_STATUS_CLOSED = 6;
    const TICKET_USER_TYPE_AUTHOR = 1;
    const TICKET_USER_TYPE_RESPONSIBLE = 2;

    private static $instance = null;

    private $db;
    private $ticket;
    private $ticketUser;
    private $itilFollowup;
    public $ticketStatus;

    /**
     * Ticket constructor.
     *
     * @param DB $db
     */
    private function __construct(DB $db)
    {
        $this->db = $db;
        $this->ticket = new GlpiTicket;
        $this->ticketUser = new Ticket_User;
        $this->itilFollowup = new ITILFollowup;
    }

    public function findById($id)
    {
        $result = false;

        foreach ($this->ticket->find(['id' => $id]) as $id => $ticket) {
            $result = $ticket;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getTicketStatus()
    {
        return $this->ticketStatus;
    }

    /**
     * @param mixed $ticketStatus
     */
    public function setTicketStatus($ticketStatus): void
    {
        $this->ticketStatus = $ticketStatus;
    }

    /**
     * @return Ticket
     */
    public static function getInstance()
    {
        global $DB;

        if(is_null(self::$instance)) {
            self::$instance = new self($DB);
        }

        return self::$instance;
    }

    public function getLastComments()
    {
        $result = [];
        $comments = $this->itilFollowup->find([
            'date' => ['>=', Cron::getPreviousMinute('Y-m-d H:i:s')],
            'itemtype' => GlpiTicket::class,
            'is_private' => 0
        ]);

        foreach ($comments as $comment) {
            $result[] = $comment;
        }

        return $result;
    }

    public function getTicketUserType($ticketsId, $usersId)
    {
        $ticketUser = $this->ticketUser->find([
            'tickets_id' => $ticketsId,
            'users_id' => $usersId
        ]);
        
        if(!empty($ticketUser)) {
            foreach ($ticketUser as $item) {
                return (int) $item['type'];
            }
        }
    }


    public function updateTicketStatus($ticketId, $statusId)
    {
        return $this->db->updateOrDie(
            GlpiTicket::getTable(),
            ['status' => $statusId],
            ['id' => $ticketId]
        );
    }

}