<?php


namespace Adv;


class Cron
{
    public static function getPreviousMinute($format)
    {
        $previousMinute = time() - MINUTE_TIMESTAMP;
        return date($format, $previousMinute);
    }

    public static function init()
    {
        $ticket = Ticket::getInstance();

        $comments = $ticket->getLastComments();
        if(!empty($comments)) {
            foreach ($comments as $comment) {

                $ticketId = $comment['items_id'];
                $userId = $comment['users_id'];

                $ticketUserType = $ticket->getTicketUserType($ticketId, $userId);

                switch ($ticketUserType) {
                    case Ticket::TICKET_USER_TYPE_AUTHOR:
                        $status = Ticket::TICKET_STATUS_IN_WORK;
                        $ticket->setTicketStatus($status);
                        break;
                    case Ticket::TICKET_USER_TYPE_RESPONSIBLE:
                        $status = Ticket::TICKET_STATUS_PENDING;
                        $ticket->setTicketStatus($status);
                        break;
                }

                $ticket->updateTicketStatus($ticketId, $ticket->getTicketStatus());
            }
        }
    }
}