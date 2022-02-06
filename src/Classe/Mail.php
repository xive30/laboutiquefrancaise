<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail {
    private $api_key = "4036887c59caf0d0769f6fb81d47fcad";
    private $api_key_secret = "b63e3d74e45387238b772ffd20743af8";
    
    public function send($to_email, $to_name, $subject, $content) {

        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']) ;
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "deflo59@gmail.com",
                        'Name' => "La Boutique Française"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3564867,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}