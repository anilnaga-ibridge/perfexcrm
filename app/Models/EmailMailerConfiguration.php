<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMailerConfiguration extends Model
{
    protected $fillable = [
        'mail_engine',
        'protocol',
        'encryption',
        'smtp_host',
        'smtp_port',
        'from_email',
        'smtp_username',
        'smtp_password',
        'charset',
        'bcc_emails',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected function casts(): array
    {
        return [
            // Laravel encrypts this value before it reaches the database and
            // decrypts it only when the application needs to use the mailer.
            'smtp_username' => 'encrypted',
            'smtp_password' => 'encrypted',
            'smtp_port' => 'integer',
        ];
    }
}
