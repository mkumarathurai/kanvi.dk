<?php

namespace Tests\Feature;

use App\Mail\AdminRecoveryMail;
use App\Mail\EventReminderMail;
use App\Mail\InvitationMail;
use App\Mail\MagicLinkMail;
use App\Mail\OrganizerMessageMail;
use App\Mail\PasswordResetMail;
use App\Mail\ResponseConfirmationMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use DOMDocument;
use DOMXPath;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Transport\ArrayTransport;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EmailDesignTest extends TestCase
{
    public static function messages(): array
    {
        $url = 'https://kanvi.example/action?token=example&context=mail';
        $title = 'Fest & venner <script>alert("test")</script>';

        return [
            'login' => [MagicLinkMail::class, [$url, 'om 15 minutter']],
            'verification' => [VerifyEmailMail::class, [$url, 'om 60 minutter']],
            'welcome' => [WelcomeMail::class, [$url, 'Mathi & venner']],
            'invitation' => [InvitationMail::class, [$title, $url, 'Mathi', ['Lørdag · kl. 17.30', 'Aarhus']]],
            'anonymous invitation' => [InvitationMail::class, [$title, $url]],
            'response' => [ResponseConfirmationMail::class, [$title, $url, ['Fredag: Kan', 'Lørdag: Ubesvaret']]],
            'reminder' => [EventReminderMail::class, [$title, $url]],
            'password reset' => [PasswordResetMail::class, [$url, 'om 60 minutter']],
            'organizer' => [OrganizerMessageMail::class, [$title, $url, 'Du har fået et nyt svar.']],
            'recovery' => [AdminRecoveryMail::class, [$title, $url, '20.30', false]],
            'registration' => [AdminRecoveryMail::class, [$title, $url, '20.30', true]],
        ];
    }

    #[DataProvider('messages')]
    public function test_delivered_mail_has_one_action_safe_content_and_a_plain_text_alternative(string $class, array $arguments): void
    {
        config(['app.url' => 'https://kanvi.example']);
        $mail = new $class(...$arguments);
        $mailer = new Mailer('test', app('view'), new ArrayTransport);
        $mail->from('hello@kanvi.example', 'Kanvi')->to('recipient@example.test');
        $message = $mailer->send($mail)->getSymfonySentMessage()->getOriginalMessage();
        $html = $message->getHtmlBody();
        $plain = $message->getTextBody();

        $this->assertSame($mail->title, $message->getSubject());
        $this->assertStringContainsString($mail->preheader, html_entity_decode($html));
        $this->assertStringContainsString($mail->actionUrl, $plain);
        $this->assertStringContainsString($mail->heading, $plain);
        foreach ($mail->details as $detail) {
            $this->assertStringContainsString($detail, $plain);
        }
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<svg', $html);
        $this->assertStringNotContainsString('display:flex', $html);
        $this->assertStringNotContainsString('display:grid', $html);
        $this->assertStringNotContainsString('var(--', $html);
        $this->assertStringNotContainsString('<table', $plain);
        $this->assertFalse($message->getHeaders()->has('List-Unsubscribe'));

        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($document);
        $actions = $xpath->query('//a[contains(@style, "background:#138448")]');
        $this->assertCount(1, $actions);
        $this->assertSame($mail->actionUrl, $actions->item(0)->getAttribute('href'));
        $this->assertSame($mail->actionLabel, trim($actions->item(0)->textContent));
        $this->assertCount(1, $xpath->query('//h1'));
        $this->assertCount(0, $xpath->query('//table[not(@role="presentation")]'));
        $logo = $xpath->query('//img')->item(0);
        $this->assertSame('Kanvi', $logo->getAttribute('alt'));
        $this->assertSame('120', $logo->getAttribute('width'));
        $this->assertSame('https://kanvi.example/images/email/kanvi-logo.png', $logo->getAttribute('src'));
        $this->assertStringContainsString('multipart/alternative', $message->toString());
    }

    public function test_recovery_keeps_expiry_and_access_warning_in_both_parts(): void
    {
        $mail = new AdminRecoveryMail('Sommerfest', 'https://kanvi.example/recover', '20.30', false);
        $mail->assertSeeInHtml('Linket kan bruges én gang og udløber kl. 20.30 (dansk tid).');
        $mail->assertSeeInText('Linket kan bruges én gang og udløber kl. 20.30 (dansk tid).');
        $mail->assertSeeInHtml('Alle med linket kan få arrangøradgang.');
        $mail->assertSeeInText('Alle med linket kan få arrangøradgang.');
    }

    public function test_action_rejects_non_http_links(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WelcomeMail('javascript:alert(1)');
    }

    public function test_logo_is_a_high_resolution_png(): void
    {
        [$width, $height, $type] = getimagesize(public_path('images/email/kanvi-logo.png'));
        $this->assertSame(IMAGETYPE_PNG, $type);
        $this->assertGreaterThanOrEqual(240, $width);
        $this->assertGreaterThan(0, $height);
        $this->assertLessThan($width, $height);
    }
}
