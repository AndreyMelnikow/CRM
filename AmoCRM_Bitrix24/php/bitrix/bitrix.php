<?php

use Bitrix24\SDK\Services\ServiceBuilderFactory;

function sendToBitrix24($name, $phone, $comment)
{
    // URL для доступа к API
    $webhookUrl = "https://b24-9pr5j1.bitrix24.ru/rest/1/xa76chjpy7326o5a/";
    
    // Создание сервиса для работы с API
    $B24 = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl);
    
    // Создание контакта
    $newContact = $B24->core->call(
        'crm.contact.add',
        [
            'fields' => [
                'NAME' => $name,
                'PHONE' => [
                    [
                        "VALUE" => $phone,
                        "VALUE_TYPE" => "MOBILE"
                    ]
                ]
            ]
        ]
    );
    
    //Создание лида и привязка к нему контакта
    $newLead = $B24->core->call(
        'crm.lead.add',
        [
            'fields' => [
                "TITLE" => 'Заявка с сайта ' . date("d.m.Y H:i:s"),
                "COMMENTS" => $comment,
                "PHONE" => [
                    [
                        "VALUE" => $phone,
                        "VALUE_TYPE" => "MOBILE"
                    ]
                ],
                // Источник
                "UF_CRM_1757947071322" => 79,
                "CONTACT_ID" => $newContact 
            ]
        ]
    );
}