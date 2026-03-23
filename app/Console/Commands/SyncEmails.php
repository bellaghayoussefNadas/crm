<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Contact;
use App\Models\Account;
use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;

class SyncEmails extends Command
{
    protected $signature = 'crm:sync-emails';
    protected $description = 'Synchronize incoming emails from IMAP and link them to contacts/accounts';

    public function handle()
    {
        $this->info("Connexion au serveur IMAP...");
        
        try {
            $client = Client::account('default');
            $client->connect();

            $folders = $client->getFolders();
            $inbox = null;

            foreach($folders as $folder){
                if($folder->name === 'INBOX'){
                    $inbox = $folder;
                    break;
                }
            }

            if (!$inbox) {
                $this->error("Dossier INBOX non trouvé.");
                return;
            }

            $this->info("Lecture des messages non lus...");
            $messages = $inbox->query()->unseen()->get();

            $this->info(count($messages) . " nouveaux messages trouvés.");

            foreach ($messages as $message) {
                $sender = $message->getFrom()[0];
                $senderEmail = $sender->mail;
                $subject = $message->getSubject();
                $body = $message->getHTMLBody() ?: $message->getTextBody();
                $messageId = $message->getMessageId();

                $this->info("Traitement de l'email de : $senderEmail");

                // Check if activity already exists for this message_id
                if (Activity::where('message_id', $messageId)->exists()) {
                    continue;
                }

                // Find Contact or Account
                $contact = Contact::where('email', $senderEmail)->first();
                $account = null;
                
                if (!$contact) {
                    $account = Account::where('email', $senderEmail)->first();
                } else {
                    $account = $contact->account;
                }

                if ($contact || $account) {
                    Activity::create([
                        'type' => 'email',
                        'subject' => $subject,
                        'description' => $body,
                        'date' => Carbon::parse($message->getDate()),
                        'contact_id' => $contact ? $contact->id : null,
                        'account_id' => $account ? $account->id : null,
                        'user_id' => 1, // Default to admin or system user
                        'message_id' => $messageId
                    ]);

                    $this->line("  ✅ Email lié à " . ($contact ? $contact->first_name : $account->name));
                } else {
                    $this->line("  ❌ Expéditeur inconnu, email ignoré.");
                }

                // Optional: mark as seen
                // $message->setFlag('Seen');
            }

            $this->info("Synchronisation terminée.");

        } catch (\Exception $e) {
            $this->error("Erreur IMAP : " . $e->getMessage());
        }
    }
}
