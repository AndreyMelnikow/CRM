<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

use Bitrix\Crm\Service\Container;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Crm\FieldMultiTable;

\Bitrix\Main\Loader::includeModule('crm');

date_default_timezone_set('Europe/Moscow');
    
try {
    $container = Container::getInstance();
    
    // Поиск существующих контактов с введённым именем и номером телефона
    $duplicateContacts = FieldMultiTable::getList([
        'select' => [
            'CONTACT_ID' => 'ELEMENT_ID',
            'CONTACT_NAME' => 'CONTACT.NAME',
            'PHONE' => 'VALUE'
        ],
        'filter' => [
            '=ENTITY_ID' => 'CONTACT',
            '=TYPE_ID' => 'PHONE',
            '=VALUE' => $phone,
            '=CONTACT.NAME' => $name
        ],
        'runtime' => [
            'CONTACT' => [
                'data_type' => '\Bitrix\Crm\ContactTable',
                'reference' => [
                    '=this.ELEMENT_ID' => 'ref.ID'
                ]
            ]
        ]
    ]);

    $contactId;

    $fetchedContacts = $duplicateContacts->fetchAll();

    // Если контакт с таким же именеи и телефоном не найден, то создаётся новый
    if(empty($fetchedContacts)) {
        // Создание контакта
        $contactFactory = $container->getFactory(CCrmOwnerType::Contact);
        $contactItem = $contactFactory->createItem([
            'NAME' => $name ?? '',
        ]);

        // Добавление контакта
        $contactResult = $contactFactory->getAddOperation($contactItem)->launch();

        if (!$contactResult->isSuccess()) {
            echo '
                <div style="text-align: center; padding: 50px 20px;">
                    <div style="font-size: 36px; margin-bottom: 20px;">
                        Успех!
                        <p>Произошла ошибка</p>
                    </div>
                </div>';
        }

        $contactId = $contactItem->getId();
    }
    // Если найден, то новый контакт не создаётся
    else {
        foreach ($fetchetContacts as $fetchetContact) {
            $fetchetContactId = $fetchetContact['CONTACT_ID'];
            $fetchetContactName = $fetchetContact['CONTACT_NAME'];
            $fetchetContactphoneNumber = $fetchetContact['PHONE'];

            if ($fetchetContactName == $name && $fetchetContactphoneNumber == $phone)
            {
                $contactId = $fetchetContactId;
                break;
            }
        }
    }
    
    
    // Создание лида
    $leadFactory = $container->getFactory(CCrmOwnerType::Lead);
    $leadItem = $leadFactory->createItem([
        'TITLE' => 'Заявка с сайта '.date('d.m.Y/H:i:s'),
        'NAME' => $name ?? '',
        'COMMENTS' => $comment ?? '',
        'CONTACT_ID' => $contactId,
        // Поле "Источник"
        'UF_CRM_1758117329596' => 26,    
    ]);
    
    // Добавление лида
    $leadResult = $leadFactory->getAddOperation($leadItem)->launch();
    
    if (!$leadResult->isSuccess()) {
        $contactFactory->getDeleteOperation($contactItem)->launch();
        echo '
            <div style="text-align: center; padding: 50px 20px;">
                <div style="font-size: 36px; margin-bottom: 20px;">
                    Успех!
                    <p>Произошла ошибка</p>
                </div>
            </div>';
    }
    
    $leadId = $leadItem->getId();
    
    // Добавление телефона к контакту и лиду
    if (!empty($phone)) {
        $parsedPhone = Parser::getInstance()->parse($phone)->format(Format::INTERNATIONAL);
        $fieldMulti = new CCrmFieldMulti();
        
        // Добавление телефона к контакту
        $fieldMulti->Add([
            'ENTITY_ID' => 'CONTACT',
            'ELEMENT_ID' => $contactId,
            'TYPE_ID' => 'PHONE',
            'VALUE' => $parsedPhone,
            'VALUE_TYPE' => 'WORK'
        ]);
        
        // Добавление телефона к лиду
        $fieldMulti->Add([
            'ENTITY_ID' => 'LEAD',
            'ELEMENT_ID' => $leadId,
            'TYPE_ID' => 'PHONE',
            'VALUE' => $parsedPhone,
            'VALUE_TYPE' => 'WORK'
        ]);
    }
    
     echo '
        <div style="text-align: center; padding: 50px 20px;">
            <div style="font-size: 36px; margin-bottom: 20px;">
                Успех!
                <p>Ваша заявка была успешно создана!</p>
            </div>
        </div>';
    
} 
catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');