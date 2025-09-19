<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Models\TagModel;
use League\OAuth2\Client\Token\AccessToken;

function sendToAmoCRM($name, $phone, $comment)
{
    require_once 'config.php';
    
    // Создание клиента API
    $apiClient = new AmoCRMApiClient(
        $client_id,
        $client_secret,
        $redirect_uri
    );
    
    // Проверка, есть ли сохранённый токен
    $access_token = null;
    if (file_exists($token_file)) {
        $tokenData = json_decode(file_get_contents($token_file), true);
        if ($tokenData && isset($tokenData['access_token'])) {
            $access_token = new AccessToken([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? '',
                'expires' => $tokenData['expires'] ?? time() + 3600
            ]);
        }
    }
    
    // Получение нового токена, если нет сохранённого
    if ($access_token === null) {
        try {
            $access_token = $apiClient->getOAuthClient()->getAccessTokenByCode($code);
            // Сохранение токена в файл
            file_put_contents($token_file, json_encode([
                'access_token' => $access_token->getToken(),
                'refresh_token' => $access_token->getRefreshToken(),
                'expires' => $access_token->getExpires()
            ]));
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Ошибка получения токена: ' . $e->getMessage()];
        }
    }
    
    $apiClient->setAccessToken($access_token)
              ->setAccountBaseDomain($subdomain . '.amocrm.ru');
    
    // Создание контакта
    $contact = new ContactModel();
    $contact->setName($name);
    
    // Добавление телефона к контакту
    $phoneField = (new MultitextCustomFieldValuesModel())
    ->setFieldId(3415891)
    ->setValues((new MultitextCustomFieldValueCollection())
    ->add((new MultitextCustomFieldValueModel())
    ->setValue($phone)));
    
    $contact->setCustomFieldsValues((new CustomFieldsValuesCollection())->add($phoneField));
    
    // Создание сделки
    $lead = new LeadModel();
    $lead->setName('Заявка с сайта '.date("d.m.Y/H:i:s"));
    
    // Добавление поля комментария
    $commentField= (new TextCustomFieldValuesModel())->setFieldId(3417301)->setValues(
        (new TextCustomFieldValueCollection())->add((new TextCustomFieldValueModel())->setValue($comment))
    );
    
    // Добавление поля источника
    $sourceField= (new TextCustomFieldValuesModel())->setFieldId(3416927)->setValues(
        (new TextCustomFieldValueCollection())->add((new TextCustomFieldValueModel())->setValue("Сайт"))
    );
    
    $leadCustomFields = new CustomFieldsValuesCollection();
    $leadCustomFields->add($commentField);
    $leadCustomFields->add($sourceField);
    $lead->setCustomFieldsValues($leadCustomFields);
    
    // Добавление тега
    $tag = new TagModel();
    $tag->setName("сайт");
    
    $tagsCollection = new TagsCollection();
    $tagsCollection->add($tag);
    $lead->setTags($tagsCollection);
    
    // Привязка контакта к сделке
    $contactsCollection = new ContactsCollection();
    $contactsCollection->add($contact);
    $lead->setContacts($contactsCollection);
    
    // Создание сделки и связанного контакта
    $lead = $apiClient->leads()->addOneComplex($lead); 
}