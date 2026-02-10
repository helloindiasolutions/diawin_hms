<?php

namespace App\Api\Services;

class WhatsAppService
{
    private $apiUrl = "https://billmsg.com/api/send/whatsapp";
    private $secret;
    private $account;

    public function __construct()
    {
        // Use the actual keys from the .env file
        $this->secret = $_ENV['WHATS_API'] ?? '';
        $this->account = $_ENV['WHATS_UNIQUE_ID'] ?? '';
    }

    /**
     * Send a WhatsApp message (Text or Document)
     *
     * @param string $recipient Phone number
     * @param string $message Text message or caption
     * @param string $type 'text' or 'document'
     * @param array $options Additional options (document_url, document_type, etc.)
     * @return array
     */
    public function send($recipient, $message, $type = 'text', $options = [])
    {
        // Format number: remove non-digits
        $recipient = preg_replace('/[^0-9]/', '', $recipient);

        // India specific logic: 10 digits -> 91 + digits
        if (strlen($recipient) == 10) {
            $recipient = "91" . $recipient;
        }

        $data = [
            "secret" => $this->secret,
            "account" => $this->account,
            "recipient" => $recipient,
            "type" => $type,
            "message" => $message,
            "priority" => 1
        ];

        if (!empty($options)) {
            $data = array_merge($data, $options);
        }

        return $this->makeRequest($data);
    }

    /**
     * Send a local file as a document
     */
    public function sendFile($recipient, $filePath, $filename, $caption = '')
    {
        if (!file_exists($filePath)) {
            \System\Logger::error("WhatsApp File not found: $filePath");
            return ['status' => false, 'message' => 'File not found'];
        }

        return $this->send($recipient, $caption, 'document', [
            'document_file' => new \CURLFile($filePath, 'application/pdf', $filename),
            'document_name' => $filename
        ]);
    }

    /**
     * Send the Bill as a PDF document
     */
    public function sendDocument($recipient, $pdfUrl, $filename, $caption = '')
    {
        // If the URL is localhost or 127.0.0.1, the remote server cannot download the file.
        // We attempt to download it locally first and then upload it as a binary file.
        if (strpos($pdfUrl, 'localhost') !== false || strpos($pdfUrl, '127.0.0.1') !== false) {
            \System\Logger::info("Local environment detected in sendDocument. Attempting binary upload.");

            try {
                // Since this is likely a local URL, we can use file_get_contents or similar
                // But safer to generate the PDF content directly if we can.
                // However, for generic support, we'll try to fetch the URL content.
                $ctx = stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]);
                $content = @file_get_contents($pdfUrl, false, $ctx);

                if ($content) {
                    $tempFile = sys_get_temp_dir() . '/wa_' . time() . '_' . rand(1000, 9999) . '.pdf';
                    file_put_contents($tempFile, $content);

                    $res = $this->sendFile($recipient, $tempFile, $filename, $caption);

                    if (file_exists($tempFile))
                        unlink($tempFile);
                    return $res;
                }
            } catch (\Exception $e) {
                \System\Logger::error("Binary upload failed: " . $e->getMessage());
            }

            // Absolute Fallback to text link if binary upload fails
            $msg = $caption . "\n\n*View Digital Copy:* " . $pdfUrl;
            return $this->send($recipient, $msg, 'text');
        }

        return $this->send($recipient, $caption, 'document', [
            'document_url' => $pdfUrl,
            'document_name' => $filename,
            'document_type' => 'pdf'
        ]);
    }

    /**
     * Make the cURL request
     */
    private function makeRequest($data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // LOG EVERYTHING FOR DEBUGGING
        $logData = [
            'url' => $this->apiUrl,
            'recipient' => $data['recipient'],
            'type' => $data['type'],
            'has_file' => isset($data['document_file']),
            'has_url' => isset($data['document_url']),
            'fields' => implode(', ', array_keys($data))
        ];
        \System\Logger::info("WhatsApp API Request: " . json_encode($logData));

        \System\Logger::info("WhatsApp API Response Status: $http_code");
        \System\Logger::info("WhatsApp API Response Body: $response");

        if ($http_code !== 200) {
            \System\Logger::error("WhatsApp API Error ($http_code): $response - $error");
            return ['status' => false, 'message' => 'API Error', 'details' => $response];
        }

        return json_decode($response, true);
    }
}
