<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email}', function () {
    $email = $this->argument('email');
    $this->info("Sending test mail to $email...");
    try {
        Illuminate\Support\Facades\Mail::raw('Ceci est un mail de test envoye via Resend.', function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Resend Agglobet');
        });
        $this->info('Email envoye avec succes !');
    } catch (\Exception $e) {
        $this->error("Erreur lors de l'envoi : " . $e->getMessage());
    }
})->purpose('Send a test email using Resend');

Schedule::command('football:sync-matches')->hourly();