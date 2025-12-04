<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventLogoTest extends TestCase
{
    // use RefreshDatabase; // Don't wipe the DB, just test logic

    public function test_event_can_be_created_with_logo()
    {
        Storage::fake('public');

        $user = User::where('email', 'admin@example.com')->first();
        if (!$user) {
            $user = User::factory()->create(['email' => 'admin@example.com']);
        }

        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($user)->post('/events', [
            'name' => 'Test Event Logo',
            'key' => 'TESTLOGO',
            'type' => 'Curso',
            'logo' => $file,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('events.index'));

        $event = Event::where('key', 'TESTLOGO')->first();
        $this->assertNotNull($event);
        $this->assertNotNull($event->logo);

        Storage::disk('public')->assertExists($event->logo);

        // Clean up
        $event->delete();
    }

    public function test_document_configuration_can_save_email_message()
    {
        $user = User::where('email', 'admin@example.com')->first();
        if (!$user) {
            $user = User::factory()->create(['email' => 'admin@example.com']);
        }

        $config = \App\Models\DocumentConfiguration::create([
            'document_name' => 'Test Config',
            'document_type' => 'Constancia',
            'is_active' => true,
            'page_orientation' => 'L',
            'page_size' => 'Letter',
            'folio_start' => 1,
            'folio_digits' => 4,
        ]);

        $response = $this->actingAs($user)->put(route('document-configurations.update', $config), [
            'document_name' => 'Test Config Updated',
            'page_orientation' => 'L',
            'page_size' => 'Letter',
            'folio_start' => 1,
            'folio_digits' => 4,
            'email_message' => 'This is a custom email message.',
        ]);

        $response->assertRedirect();

        $config->refresh();
        $this->assertEquals('This is a custom email message.', $config->email_message);

        // Clean up
        $config->delete();
    }

    public function test_certificate_mail_content()
    {
        Storage::fake('public');
        $logo = UploadedFile::fake()->image('logo.png');
        $path = $logo->store('event_logos', 'public');

        $mail = new \App\Mail\CertificateMail(
            'PDF CONTENT',
            'certificate.pdf',
            'John Doe',
            'Test Event',
            'Constancia de Prueba',
            $path,
            'Custom Email Message'
        );

        $mail->assertSeeInHtml('Custom Email Message');

        $this->assertEquals($path, $mail->logo);
        $this->assertEquals('Custom Email Message', $mail->emailMessage);

        $rendered = $mail->render();
        $this->assertStringContainsString('Custom Email Message', $rendered);
        // The logo path in the rendered HTML will be a CID or base64, so checking for the exact path might fail.
        // But we can check if it contains an img tag.
        $this->assertStringContainsString('<img', $rendered);
    }
}
