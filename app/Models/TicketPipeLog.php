<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPipeLog extends Model
{
    protected $table = 'ticket_pipe_logs';

    protected $fillable = [
        'from_name',
        'from_email',
        'to',
        'subject',
        'message',
        'status',
    ];
}
