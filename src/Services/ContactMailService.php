<?php

namespace IO\Services;

use Plenty\Plugin\Mail\Contracts\MailerContract;
use Plenty\Plugin\Templates\Twig;
use IO\Services\TemplateConfigService;
use IO\Validators\Customer\ContactFormValidator;

class ContactMailService
{
    private $name = '';
    private $message = '';
    private $orderId = '';
    
    public function __construct()
    {
    
    }
    
    public function sendMail($mailTemplate, $contactData = [])
    {
        ContactFormValidator::validateOrFail($contactData);
    
        /**
         * @var TemplateConfigService $templateConfigService
         */
        $templateConfigService = pluginApp(TemplateConfigService::class);
        $recipient = $templateConfigService->get('contact.shop_mail');
        
        if(!strlen($recipient) || !strlen($mailTemplate))
        {
            return false;
        }
        
        /**
         * @var Twig
         */
        $twig = pluginApp(Twig::class);
    
        $mailtemplateParams = [
            'name'     => $contactData['name'],
            'message'  => nl2br($contactData['message']),
            'orderId'  => $contactData['orderId'],
            'userMail' => $contactData['userMail']
        ];
    
        $renderedMailTemplate = $twig->render($mailTemplate, $mailtemplateParams);
        
        if(!strlen($renderedMailTemplate))
        {
            return false;
        }
        
        /**
         * @var MailerContract $mailer
         */
        $mailer = pluginApp(MailerContract::class);
        $mailer->sendHtml($renderedMailTemplate, $recipient, $contactData['subject']);
        
        return true;
    }
}