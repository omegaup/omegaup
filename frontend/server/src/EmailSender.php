<?php

namespace OmegaUp;

interface EmailSender {
    /**
     * @param string[] $emails
     * @param string $subject
     * @param string $body
     */
    public function sendEmail(array $emails, string $subject, string $body) : void;
}
