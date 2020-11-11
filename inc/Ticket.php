<?php


namespace Adv;


class Ticket extends \CommonDBTM
{
    const TICKET_STATUS_IN_WORK = 2;
    const TICKET_STATUS_PENDING = 4;
    const TICKET_USER_TYPE_AUTHOR = 1;
    const TICKET_USER_TYPE_RESPONSIBLE = 2;

    private static $instance = null;
    private $db;
    private $ticketUser;

    /**
     * Ticket constructor.
     *
     * @param \DB $db
     */
    private function __construct(\DB $db)
    {
        $this->db = $db;
        $this->ticketUser = new \Ticket_User;
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
        $comments = $this->db->request(\ITILFollowup::getTable(), [
            'date' => ['>=', Cron::getPreviousMinute('Y-m-d H:i:s')],
            'itemtype' => \Ticket::class,
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
            \Ticket::getTable(),
            ['status' => $statusId],
            ['id' => $ticketId]
        );
    }

}