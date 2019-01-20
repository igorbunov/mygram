<?php

namespace App\Http\Controllers;

use App\Chatbot;
use App\ChatbotAccounts;
use App\FastTask;
use App\Tariff;
use App\User;
use const Grpc\CHANNEL_CONNECTING;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function runTests()
    {
        $bot = new BotController();

        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день. Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Добрый день']
        ]);

        $this->checkTest(1, $res, $bot::STATUS_WAITING_ANSWER,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день. Предлагаю работу в Инстаграм. Интересно?'],
            ['isMy' => false, 'text' => 'Добрый день, да']
        ]);

        $this->checkTest(2, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Доброго времени суток. Предлагаю работу в Инстаграм. Интересно?'],
            ['isMy' => false, 'text' => 'нет']
        ]);

        $this->checkTest(3, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'привет, как дела'],
            ['isMy' => false, 'text' => 'норм, а у тебя'],
            ['isMy' => true, 'text' => 'так себе']
        ]);

        $this->checkTest(4, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет, как дела?'],
            ['isMy' => false, 'text' => 'Привет, нормально, а у тебя?'],
            ['isMy' => true, 'text' => 'Предлагаю работу в Инстаграм. Интересно?'],
            ['isMy' => false, 'text' => 'Это оря?'],
            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю. '],
            ['isMy' => false, 'text' => 'Да, давайте'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'надо подумать'],
            ['isMy' => true, 'text' => 'думайте быстрее'],
            ['isMy' => false, 'text' => '345345435'],
        ]);

        $this->checkTest(5, $res, $bot::STATUS_DIALOG_FINISHED,'','345345435');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет, как дела?'],
            ['isMy' => false, 'text' => 'Привет, нормально, а у тебя?'],
            ['isMy' => true, 'text' => 'Предлагаю работу в Инстаграм. Интересно?'],
            ['isMy' => false, 'text' => 'Это орифлейм?'],
            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю. '],
            ['isMy' => false, 'text' => 'Да, давайте'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'надо подумать'],
            ['isMy' => true, 'text' => 'думайте быстрее']
        ]);

        $this->checkTest(6, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет, как дела?'],
            ['isMy' => false, 'text' => 'Привет, нормально, а у тебя?'],
            ['isMy' => true, 'text' => 'Предлагаю Интересно?']
        ]);

        $this->checkTest(7, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'сама ищу)']
        ]);

        $this->checkTest(8, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'в чём заключается работа']
        ]);

        $this->checkTest(9, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да нет']
        ]);

        $this->checkTest(10, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'доброго. и так работаем уже)']
        ]);

        $this->checkTest(11, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'yes'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'да']
        ]);

        $this->checkTest(12, $res, $bot::STATUS_WAITING_ANSWER,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'здравствуйте! блaгодapю! я сотрудничаю с корейской компанией атоми. рассказать подробнее?']
        ]);

        $this->checkTest(13, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да, хочу минет']
        ]);

        $this->checkTest(14, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'пизда']
        ]);

        $this->checkTest(15, $res, $bot::STATUS_WAITING_ANSWER,'','');
//dd($res);
        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'а'],
            ['isMy' => false, 'text' => 'да']
        ]);

        $this->checkTest(16, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день. Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Благодарю, я ваша коллега в Орифлейм. Вам успехов!!!']
        ]);

        $this->checkTest(17, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день. Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Вибачте но не хочу']
        ]);

        $this->checkTest(18, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день. Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'говори']
        ]);

        $this->checkTest(19, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Предлагаю работу в Инстаграм. Интересно?'],
            ['isMy' => false, 'text' => 'Это оря?']
        ]);

        $this->checkTest(20, $res, $bot::STATUS_DIALOG_FINISHED,'','', true);
//dd($res);
        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'yes'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'это орифлейм, ситивой или маркетинг?']
        ]);

        $this->checkTest(21, $res, $bot::STATUS_DIALOG_FINISHED,'','', true);
//dd($res);
        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'yes'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'это орифлейм, ситивой или маркетинг?'],
//            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'],
            ['isMy' => false, 'text' => '345345345']
        ]);

        $this->checkTest(22, $res, $bot::STATUS_DIALOG_FINISHED,'','345345345', false);

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'yes'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'ат спасибы']
        ]);

        $this->checkTest(23, $res, '','','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'like'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'щельбе кельбе']
        ]);

        $this->checkTest(24, $res, '','','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Здравствуйте, я работаю, спасибо']
        ]);

        $this->checkTest(25, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'интересно'],
            ['isMy' => false, 'text' => 'что за компания'],
            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'],
            ['isMy' => false, 'text' => 'Благодарю. Я уже в команде Орифлейм']
        ]);

        $this->checkTest(26, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'привет, трави']
        ]);

        $this->checkTest(27, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Добрый день! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Добрый день, в чем заключается работа'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'в чем заключается работа']
        ]);

        $this->checkTest(28, $res, $bot::STATUS_WAITING_ANSWER,'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Отличный профиль'],
            ['isMy' => false, 'text' => 'Добрый день, спасибо, и у вас'],
            ['isMy' => true, 'text' => 'Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да, в чем заключается работа'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'в чем заключается работа']
        ]);

        $this->checkTest(29, $res, $bot::STATUS_WAITING_ANSWER,'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Отличный профиль'],
            ['isMy' => false, 'text' => 'Добрый день, спасибо, и у вас'],
            ['isMy' => true, 'text' => 'Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да, в чем заключается работа'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'с чем именно связанна работа'],
            ['isMy' => true, 'text' => 'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.'],
            ['isMy' => false, 'text' => 'это сетевой']
        ]);

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Отличный профиль'],
            ['isMy' => false, 'text' => 'Добрый день, спасибо, и у вас'],
            ['isMy' => true, 'text' => 'Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да, в чем заключается работа'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'с чем именно связанна работа'],
            ['isMy' => true, 'text' => 'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.'],
            ['isMy' => false, 'text' => 'это сетевой'],
            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'],
            ['isMy' => false, 'text' => '254345345'],

        ]);

        $this->checkTest(31, $res, $bot::STATUS_DIALOG_FINISHED,'','254345345');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'да, в чем заключается работа'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'это сетевой'],
            ['isMy' => true, 'text' => 'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'],
            ['isMy' => false, 'text' => 'с чем именно связанна работа'],
            ['isMy' => true, 'text' => 'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.'],
            ['isMy' => false, 'text' => 'подробнее можно']

        ]);

        $this->checkTest(32, $res, '','','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Здравствуйте']

        ]);

        $this->checkTest(33, $res, $bot::STATUS_WAITING_ANSWER,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Спасибо не интересует(боюсь что не справлюсь)']

        ]);

        $this->checkTest(34, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Доброго вечора, мене цікавить ваша пропозиція.']

        ]);

        $this->checkTest(35, $res, $bot::STATUS_WAITING_ANSWER,'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Да'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'В двух словах можно с чем связана работа?']

        ]);

        $this->checkTest(36, $res, $bot::STATUS_WAITING_ANSWER,'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Да'],
            ['isMy' => true, 'text' => 'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'],
            ['isMy' => false, 'text' => 'Хотя бы вид деятельности']

        ]);

        $this->checkTest(37, $res, $bot::STATUS_WAITING_ANSWER,'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'Здравствуйте 😊 Приглашаем спонсоров в наш GIVEAWAY🎉 @_aktive_winner  🚗Дарим Автокресло 🎁  💳Участие 120грн с постами/ 150грн без постов. ‌📣Старт (22-23 января)  👥Приход 1000+ @_aktive_winner_like 🎉Дарим детскую кухню или мастерскую на выбор🎁  💳Участие 80грн с постами/120 грн без  📣Старт (20-21 января)  👥Приход 600-700+  💕LIKE TIME бесплатно Вы с нами?']

        ]);

        $this->checkTest(38, $res, $bot::STATUS_DIALOG_FINISHED,'','');

        unset($bot); $bot = new BotController();
        $res = $bot->getAnswer([
            ['isMy' => true, 'text' => 'Привет! Предлагаю работу в Instagram. Интересно?'],
            ['isMy' => false, 'text' => 'сама могу предложить']

        ]);

        $this->checkTest(39, $res, $bot::STATUS_DIALOG_FINISHED,'','');

//        dd($res);
    }

    private function checkTest(int $number, array $response, string $status, string $txt, string $phone, bool $isOri = false) {
        if ($response['status'] == $status and $response['txt'] == $txt and $response['phone'] == $phone and $response['ori'] == $isOri) {
            echo '<span style="color: green;font-weight: bold;font-size: 16px;">test # '. $number . ' good</span><br/>';
        } else {
            echo '<span style="color: red;font-weight: bold;font-size: 22px;">test # '. $number . ' error</span><br/>';
        }
    }

    public function toggleAccount(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $nickname = (string) $req->post('nickname', '');
        $isChecked = (int) $req->post('isChecked', -1);

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $chatBot = Chatbot::getByUserId($userId);

        if (is_null($chatBot)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения чатбота']);
        }

        if ($nickname == '') {
            return response()->json(['success' => false, 'message' => 'Не указан никнейм']);
        }
        if ($isChecked == -1) {
            return response()->json(['success' => false, 'message' => 'Не указан статус']);
        }

        $res = ChatbotAccounts::setIsInSendlist($chatBot, $nickname, $isChecked);

        if (!$res) {
            return response()->json(['success' => false,'message' => 'Ошибка, не удалось изменить статус']);
        }

        //Safelist::updateStatistics($safeList->id);

        return response()->json(['success' => true, 'is_checked' => $isChecked]);
    }

    public function getChatbotAccountsAjax(Request $req)
    {
        $userId = (int) session('user_id', 0);
        $start = (int) $req->post('start', 0);
        $limit = 50;

        if ($userId == 0) {
            return response()->json(['success' => false, 'message' => 'Потеряна сессия авторизации']);
        }

        $tariff = Tariff::getUserCurrentTariff($userId);

        if (is_null($tariff)) {
            return response()->json(['success' => false, 'message' => 'Не удалось получить тариф']);
        }

        $chatBot = Chatbot::getByUserId($userId);

        if (is_null($chatBot)) {
            return response()->json(['success' => false, 'message' => 'Ошибка получения чатбота']);
        }

        $allAccounts = ChatbotAccounts::getAll($chatBot, $start, $limit);

        return view('chatbot.chatbot_account_item', [
            'chatBotAccounts' => $allAccounts['data'],
            'chatBotAccountsTotal' => $allAccounts['total'],
            'start' => $start,
            'limit' => $limit
        ]);
    }

    public function index()
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return redirect('login');
//            return view('main_not_logined');
        }

        $accounts = User::getAccountsByUser($userId, true);

        $chatbot = Chatbot::getByUserId($userId);
//dd($userId, $chatbot->id);
        if (is_null($chatbot)) {
            Chatbot::add(['user_id' => $userId, 'hashtags' => '']);

            $chatbot = Chatbot::getByUserId($userId);
        }

        $chatbot->hashtags = str_replace('|', "\n", $chatbot->hashtags);

        switch($chatbot->status) {
            case Chatbot::STATUS_EMPTY:
                $chatbot->statusRus = 'Пусто';
                break;
            case Chatbot::STATUS_UPDATING:
                $chatbot->statusRus = 'Загрузка списка';
                break;
            case Chatbot::STATUS_FINISHED:
                $chatbot->statusRus = 'Выполнен';
                break;
            case Chatbot::STATUS_SYNCHRONIZED:
                $chatbot->statusRus = 'Список загружен';
                break;
            case Chatbot::STATUS_IN_PROGRESS:
                $chatbot->statusRus = 'В процессе';
                break;
        }

        $allAccounts = ChatbotAccounts::getAll($chatbot);



        $chatbotStats = Chatbot::getStats($chatbot);
        $statsByAccount = ChatbotAccounts::getDirectStats($chatbot);

        foreach($statsByAccount as $i => $acc) {
            $statsByAccount[$i]->delay = FastTask::getDelayForAccount($acc->sender_account_id, FastTask::TYPE_SEND_FIRST_CHATBOT_MESSAGE);
        }

        $takenPhonesToday = ChatbotAccounts::getTakenPhones($chatbot, true);
        $takenPhonesAllTime = ChatbotAccounts::getTakenPhones($chatbot, false);
        $takenPhonesTodayCount = count($takenPhonesToday);
        $takenPhonesAllTimeCount = count($takenPhonesAllTime);

        $takenPhonesToday = \json_encode($takenPhonesToday);
        $takenPhonesAllTime = \json_encode($takenPhonesAllTime);

        $res = [
            'title' => 'Чат бот'
            , 'activePage' => 'chatbot'
            , 'accounts' => $accounts
            , 'chatbot' => $chatbot
            , 'chatbotStats' => $chatbotStats
            , 'statsByAccount' => $statsByAccount
            , 'currentTariff' => Tariff::getUserCurrentTariffForMainView($userId)
            , 'accountPicture' => User::getAccountPictureUrl($userId)
            , 'chatBotAccounts' => $allAccounts['data']
            , 'chatBotAccountsTotal' => $allAccounts['total']
            , 'start' => 0
            , 'limit' => 50
            , 'takenPhonesToday' => $takenPhonesToday
            , 'takenPhonesAllTime' => $takenPhonesAllTime
            , 'takenPhonesTodayCount' => $takenPhonesTodayCount
            , 'takenPhonesAllTimeCount' => $takenPhonesAllTimeCount
        ];




        return view('chatbot.main', $res);
    }

    public function setStatus(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        $status = (string) $req->post('status', '');

        if (!in_array($status, [Chatbot::STATUS_IN_PROGRESS, Chatbot::STATUS_SYNCHRONIZED
            , Chatbot::STATUS_FINISHED, Chatbot::STATUS_UPDATING, Chatbot::STATUS_EMPTY])) {
            return response()->json(['success' => false, 'error' => 'Не верный статус']);
        }

        $chatbot = Chatbot::getByUserId($userId);

        if (is_null($chatbot)) {
            return response()->json(['success' => false, 'error' => 'Не удалось получить чатбота']);
        }

        Chatbot::setStatus($chatbot->id, $status);

        if ($status == Chatbot::STATUS_IN_PROGRESS) {
            ChatbotAccounts::deleteUnselected($chatbot);
        }

        return response()->json(['success' => true]);
    }

    public function updateList(Request $req)
    {
        $userId = (int) session('user_id', 0);

        if ($userId == 0) {
            return response()->json(['success' => false, 'error' => 'Необходимо авторизоваться']);
        }

        //TODO: проверить тариф и доступность этого таска

        $hashtags = (string) $req->post('hashtags', '');
        $hashtags = trim($hashtags);

        if (empty($hashtags)) {
            return response()->json(['success' => false, 'error' => 'Вы не указали хештеги']);
        }

        $hashtags = explode("|", $hashtags);

        foreach($hashtags as $i => $hashtag) {
            $hashtags[$i] = str_replace("#", "", $hashtag);
        }

        $workWithDirectAnswerTask = (int) $req->post('work_with_direct_answer_task', '0');
        $maxAccounts = (int) $req->post('max_accounts', '100');

        $chatbot = Chatbot::getByUserId($userId);

        if (is_null($chatbot)) {
            Chatbot::add([
                'user_id' => $userId,
                'hashtags' => implode('|', $hashtags),
                'max_accounts' => $maxAccounts,
                'work_with_direct_answer_task' => $workWithDirectAnswerTask,
                'status' => Chatbot::STATUS_UPDATING
            ]);

            $chatbot = Chatbot::getByUserId($userId);
        }

        Chatbot::edit([
            'id' => $chatbot->id,
            'hashtags' => implode('|', $hashtags),
            'max_accounts' => $maxAccounts,
            'work_with_direct_answer_task' => $workWithDirectAnswerTask,
            'status' => Chatbot::STATUS_UPDATING
        ]);


        $chatbotId = $chatbot->id;

        if ($chatbotId == 0) {
            return response()->json(['success' => false, 'error' => 'Ошибка создания чатбота']);
        }

        $accounts = User::getAccountsByUser($userId, true);

        if (count($accounts) == 0) {
            return response()->json(['success' => false, 'error' => 'Ошибка, необходимо чтоб был хотябы один активный аккаунт']);
        }

        $accountId = $accounts[0]->id;

        $fastTaskId = FastTask::addTask($accountId, FastTask::TYPE_REFRESH_CHATBOT_LIST, $chatbotId);

        return response()->json(['success' => true, 'fastTaskId' => $fastTaskId]);
    }
}
