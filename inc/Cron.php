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

                $currentStatus = $ticket->getTicketStatus($ticketId);

                if($currentStatus !== $ticket::TICKET_STATUS_CLOSED) {

                    $ticketUserType = $ticket->getTicketUserType($ticketId, $userId);

                    switch ($ticketUserType) {
                        case $ticket::TICKET_USER_TYPE_AUTHOR:
                            $status = Ticket::TICKET_STATUS_IN_WORK;
                            $ticket->setStatus($status);
                            break;
                        case $ticket::TICKET_USER_TYPE_RESPONSIBLE:
                            $status = Ticket::TICKET_STATUS_PENDING;
                            $ticket->setStatus($status);
                            break;
                    }

                    $ticket->updateTicketStatus($ticketId, $ticket->getStatus());

                }

            }
        }
    }
}