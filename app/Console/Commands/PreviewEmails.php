<?php

namespace App\Console\Commands;

use App\Mail\AdminRecoveryMail;
use App\Mail\EventReminderMail;
use App\Mail\InvitationMail;
use App\Mail\MagicLinkMail;
use App\Mail\OrganizerMessageMail;
use App\Mail\PasswordResetMail;
use App\Mail\ResponseConfirmationMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\File;

class PreviewEmails extends Command
{
    protected $signature = 'kanvi:preview-emails';

    protected $description = 'Eksportér maildesign som HTML, tekst og EML med fiktive data. Sender ingen mails.';

    public function handle(): int
    {
        $directory = storage_path('app/private/mail-previews');
        File::ensureDirectoryExists($directory);
        File::copy(public_path('images/email/kanvi-logo.png'), $directory.'/kanvi-logo.png');

        // Dedicated in-memory transport: never resolves the configured production mailer.
        $mailer = new Mailer('kanvi-preview', app('view'), new ArrayTransport);
        $url = 'https://example.test/kanvi/demo';
        $event = 'Sofies 40 års fødselsdag';
        $details = ['Lørdag 17. oktober 2026 · kl. 17.30', 'Aarhus'];
        $samples = [
            'magic-link' => new MagicLinkMail($url, 'om 15 minutter'),
            'verify-email' => new VerifyEmailMail($url, 'om 60 minutter'),
            'welcome' => new WelcomeMail($url, 'Mathi'),
            'invitation' => new InvitationMail($event, $url, 'Mathi', $details),
            'response-confirmation' => new ResponseConfirmationMail($event, $url, ['Du har svaret: Jeg kommer']),
            'event-reminder' => new EventReminderMail($event, $url, $details),
            'password-reset' => new PasswordResetMail($url, 'om 60 minutter'),
            'organizer-message' => new OrganizerMessageMail($event, $url, 'Der er kommet et nyt svar. Åbn overblikket for at se, hvem der kan være med.'),
            'admin-recovery' => new AdminRecoveryMail('Sommerfest med naboerne', $url, '20.30', false),
            'admin-registration' => new AdminRecoveryMail('Sommerfest med naboerne', $url, '20.30', true),
        ];
        $links = [];
        foreach ($samples as $name => $mail) {
            $mail->from('preview@kanvi.test', 'Kanvi')->to('example@example.test');
            $message = $mailer->send($mail)->getSymfonySentMessage()->getOriginalMessage();
            // Local HTML files can be reviewed offline, including their logo.
            $html = str_replace(rtrim(config('app.url'), '/').'/images/email/kanvi-logo.png', 'kanvi-logo.png', $message->getHtmlBody());
            File::put($directory.'/'.$name.'.html', $html);
            File::put($directory.'/'.$name.'.txt', $message->getTextBody());
            File::put($directory.'/'.$name.'.eml', $message->toString());
            $links[] = '<li><a href="'.$name.'.html">'.e($mail->title).'</a> · <a href="'.$name.'.txt">Tekst</a> · <a href="'.$name.'.eml">EML</a></li>';
        }
        File::put($directory.'/index.html', '<!doctype html><html lang="da"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kanvi · Mailpreviews</title><body style="font:16px/1.8 Arial,sans-serif;max-width:800px;margin:40px auto;padding:16px"><h1>Kanvis mails</h1><p>Fiktive eksempler. Ingen mails er sendt. Handlingslinks peger på example.test.</p><p><a href="responsive.html">Sammenlign desktop og mobil</a></p><ul>'.implode('', $links).'</ul></body></html>');
        $frames = '';
        foreach (['invitation', 'magic-link', 'admin-recovery'] as $name) {
            $frames .= '<h2>'.e($samples[$name]->title).'</h2><div style="display:flex;gap:24px;align-items:flex-start;">';
            foreach ([640, 390, 320] as $width) {
                $frames .= '<div><p>'.$width.' px</p><iframe title="'.e($samples[$name]->title).' – '.$width.' px" src="'.$name.'.html" width="'.$width.'" height="1000" style="border:1px solid #ddd;"></iframe></div>';
            }
            $frames .= '</div>';
        }
        File::put($directory.'/responsive.html', '<!doctype html><html lang="da"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kanvi · Responsive mailpreviews</title><body style="font:16px/1.6 Arial,sans-serif;margin:24px"><h1>Desktop og mobil</h1><p>Browserpreview med separate viewports. Dette erstatter ikke test i mailklienter.</p>'.$frames.'</body></html>');
        $this->info('10 mailpreviews er klar: '.$directory.'/index.html');
        $this->comment('HTML-preview er ikke en mailklienttest. Kontrollér også EML-filerne i de ønskede mailklienter.');

        return self::SUCCESS;
    }
}
