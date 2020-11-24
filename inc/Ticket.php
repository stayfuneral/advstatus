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

    /**
     * @var DB
     */
    private $db;
    /**
     * @var GlpiTicket
     */
    private $ticket;
    /**
     * @var Ticket_User
     */
    private $ticketUser;
    /**
     * @var ITILFollowup
     */
    private $itilFollowup;

    public $status;

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

        parent::__construct();
    }

    /**
     * Поиск заявки по ID
     *
     * @param $id ID заявки
     *
     * @return array|bool
     */
    public function findById($id)
    {
        $result = false;

        foreach ($this->ticket->find(['id' => $id]) as $id => $ticket) {
            $result = $ticket;
        }

        return $result;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * Текущий статус заявки
     *
     * @param $ticketId ID заявки
     *
     * @return int
     */
    public function getTicketStatus($ticketId)
    {
        $ticket = $this->findById($ticketId);
        return (int) $ticket['status'];
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

    /**
     * Список комментариев по заявкам за последнюю минуту
     *
     * @return array
     */
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

    /**
     * Тип пользователя, отправившего комментарий (инициатор заявки или специалист)
     *
     * @param $ticketsId
     * @param $usersId
     *
     * @return int
     */
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

    /**
     * Смена статуса заявки
     *
     * @param $ticketId ID заявки
     * @param $statusId ID статуса
     *
     * @return bool|\mysqli_result
     */
    public function updateTicketStatus($ticketId, $statusId)
    {
        return $this->db->updateOrDie(
            GlpiTicket::getTable(),
            ['status' => $statusId],
            ['id' => $ticketId],
            $this->db->error()
        );
    }

}