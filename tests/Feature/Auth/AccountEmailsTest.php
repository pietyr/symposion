<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;

test('the email verification message is in Polish', function () {
    $mail = (new VerifyEmail)->toMail(User::factory()->unverified()->create());

    expect($mail->subject)->toBe('Potwierdź adres e-mail')
        ->and((string) $mail->render())->toContain(
            'Dzień dobry!',
            'Kliknij przycisk poniżej, aby potwierdzić adres e-mail.',
            'Jeśli to nie Ty zakładasz konto, zignoruj tę wiadomość.',
            'Pozdrawiamy,',
            'Jeśli przycisk „Potwierdź adres e-mail” nie działa',
            'Wszelkie prawa zastrzeżone.',
        );
});

test('the password reset message is in Polish', function () {
    $mail = (new ResetPassword('token'))->toMail(User::factory()->create());

    expect($mail->subject)->toBe('Zresetuj hasło')
        ->and((string) $mail->render())->toContain(
            'Otrzymujesz tę wiadomość, ponieważ ktoś poprosił o zresetowanie hasła do Twojego konta.',
            'Link do zresetowania hasła wygaśnie za 60 min.',
            'Jeśli to nie Ty prosisz o zresetowanie hasła, zignoruj tę wiadomość.',
        );
});
