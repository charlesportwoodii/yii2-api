<?php

namespace tests\_support\traits;

use Mohrekopp\MailHogClient\MailHogClient;
use Mohrekopp\MailHogClient\SearchCriteria;
use Mohrekopp\MailHogClient\Model\Message\Message;
use Yii;

trait EmailTrait
{
    /**
     * The client URI string
     *
     * @var string
     */
    protected $clientUri = 'http://mailhog:8025';

    /**
     * Checks whether or not we should run actions that interface with MailHog
     *
     * @return boolean
     */
    protected function shouldRun()
    {
        $config = include __DIR__ . '/../../config/console.php';
        
        if ($config['components']['mailer']['transport']['host'] === 'mailhog') {
            return true;
        }

        return false;
    }

    /**
     * Returns an instasnce of the MailHogClient
     *
     * @return MailHogClient
     */
    protected function getMailHogClient()
    {
        $config = include __DIR__ . '/../../config/console.php';

        if (!$this->shouldRun()) {
            throw new \PHPUnit_Framework_SkippedTestError('Mailhog SMTP transport is not used - skipping tests that rely on SMTP emails.');
        }

        return new MailHogClient($this->clientUri);
    }

    /**
     * Deletes all emails from MailHog via the API
     *
     * @return void
     */
    public function deleteAllMessages()
    {
        if (!$this->shouldRun()) {
            return;
        }

        $request = Yii::$app->httpclient->getClient()
            ->createRequest()
            ->setUrl($this->clientUri . '/api/v1/messages')
            ->setMethod('delete')
            ->send();
    }

    /**
     * Searches Mailhog for a given email by a destination and a subject
     *
     * @param  string  $to
     * @param  string  $subject
     * @param  integer $waitTimeout
     * @param  integer $dealineTmeout
     * @return Message
     */
    public function getEmailByDestinationAndSubject($to, $subject, $waitTimeout = 1000, $dealineTmeout = 5000000) : Message
    {
        $client = $this->getMailHogClient();
        $criteria = SearchCriteria::createSentToCriteria($to);
        $results = null;
        $count = 0;
        while ($count < $dealineTmeout) {
            $count += $waitTimeout;
            
            $results = $client->searchMessages($criteria);
            // Search through all the messages until we find one that matches the subject, and the reciever
            foreach ($results as $message) {
                // We've already filtered by the reciever, so if the subject matches, return it
                if ($message->getSubject() === $subject) {
                    return $message;
                }
            }

            // If a message is not found, wait for the waitTimeout
            usleep($waitTimeout);
        }

        expect('Max wait time exceeded', true)->false();
        return null;
    }

    /**
     * Extracts a URL token string from an email body and returns the code
     *
     * @param  Message $message
     * @return void
     */
    public function extractTokenFromMessage(Message $message = null)
    {
        if ($message === null) {
            throw new \Exception('Invalid message was provided.');
        }

        preg_match_all(
            '/(https)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/',
            str_replace(['=', "\n", "\r"], '', $message->getBody()),
            $match
        );
        
        if (isset($match[0][1])) {
            $url = \explode('">', $match[0][1])[0];
        } else {
            $url = $match[0][0];
        }
        return \explode('/', \str_replace('.', '', \parse_url($url)['path']))[2];
    }
}
