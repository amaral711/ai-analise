<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotDisposableEmail implements ValidationRule
{
    private const BLOCKED = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', 'guerrillamail.net',
        'guerrillamail.org', 'guerrillamail.de', 'guerrillamailblock.com', 'spam4.me',
        'trashmail.com', 'trashmail.me', 'trashmail.net', 'trashmail.at', 'trashmail.io',
        'tempmail.com', 'temp-mail.org', 'temp-mail.ru', 'tempr.email',
        'fakeinbox.com', 'throwam.com', 'throwam.net', 'yopmail.com', 'yopmail.fr',
        'cool.fr.nf', 'jetable.fr.nf', 'nospam.ze.tc', 'nomail.xl.cx', 'mega.zik.dj',
        'speed.1s.fr', 'courriel.fr.nf', 'moncourrier.fr.nf', 'monemail.fr.nf',
        'monmail.fr.nf', 'sharklasers.com', 'guerrillamail.biz', 'grr.la', 'guerrillamailblock.com',
        'spam.la', 'maildrop.cc', 'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org',
        'dispostable.com', 'mailnull.com', 'spammotel.com', 'amilegit.com', 'fakedemail.com',
        'mailnew.com', 'mailscrap.com', 'spamfree24.org', 'spamfree24.de', 'spamfree24.eu',
        'spamfree24.info', 'spamfree24.net', 'einrot.com', 'filzmail.com', 'trillianpro.com',
        'hatespam.org', 'thisisnotmyrealemail.com', 'dontsendmespam.de', 'binkmail.com',
        'mailexpire.com', 'spamex.com', 'discard.email', 'throwam.com', 'mailnesia.com',
        'mailnull.com', 'spamgourmet.com', 'spam.su', 'throwam.com', 'trbvm.com',
        'trashmail.at', 'trashmail.me', 'trashmail.io', 'tempinbox.com', 'spamfree.eu',
        'mailtemp.info', 'getairmail.com', 'armyspy.com', 'cuvox.de', 'dayrep.com',
        'einrot.de', 'fleckens.hu', 'gustr.com', 'jourrapide.com', 'rhyta.com',
        'superrito.com', 'teleworm.us', 'mail.tm',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr($value, '@'), 1));

        if (in_array($domain, self::BLOCKED, true)) {
            $fail('Este domínio de e-mail não é permitido. Use um e-mail válido.');
        }
    }
}
