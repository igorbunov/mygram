<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 04.01.19
 * Time: 1:16
 */

namespace App\Http\Controllers;

class BotController
{
    const STATUS_WAITING_ANSWER = 'waiting_answer';
    const STATUS_DIALOG_FINISHED = 'dialog_finished';

    public function __construct()
    {
    }

    private $positiveAnswers = [
        'да', 'интересно', 'интересует', 'ага', 'угу', 'давайте', 'давай', 'хорошо',  'трави', 'говори',
        'рассказывай', 'раскажите', 'раскажи', 'расскажи', 'игтересно', 'готов', 'готова', 'отлично',
        'yes', 'yep', 'like', 'ok', 'okey', 'okay', 'канешно', 'канечно', 'конечно',
        'согласен', 'согласна', 'оке', 'ок',
        'так', 'цікаво', 'готовий', 'добре', 'розповідай', 'розкажіть', 'розкажи', 'відмінно'
    ];

    private $negativeAnswers = [
        'нет', 'не интересно', 'уже есть', 'уже работаю', 'есть работа', 'нет спасибо', 'неинтересно',
        'ні', 'не цікаво', 'не цікавить', 'не потрібно', 'не цікаво', 'вже є', 'вже працюю',
        'є робота', 'е робота', 'ні дякую', 'нецікаво',
        'no', 'nope', 'no thanks', 'no need', 'not'
    ];

    private $viberOferQuestions = [
        'поподробнее', 'что за работа', 'что за робота', 'что делать', 'расскажите', 'суть', 'условия',
        'какую', 'как', 'возможно', 'о чем', 'подробнее', 'раскажи', 'расскажи', 'именно',
        'имено', 'ват', 'подробности', 'нужно делать', 'нужна делать', 'что',
        'детальніше', 'що за робота', 'що за работа', 'що робити', 'що робити', 'що потрібно',
        'що треба', 'розкажіть', 'роскажіть', 'умови', 'яку', 'як', 'можливо', 'про що', 'про шо',
        'детальніше', 'розкажи', 'саме', 'подробиці', 'потрібно робити', 'що', '?', '??', '???'
    ];

    private $oriQuestions = [
        'орифлейм', 'ори', 'сетевой', 'продажи', 'реклама', 'маркетинг', 'эйвон', 'джерелия',
        'продавать', 'рекламировать', 'фаберлик', 'косметика', 'ori', 'oriflame', 'faberlic',
        'jerelia', 'джерелія', 'орі', 'оріфлейм', 'фаберлік', 'фармаси', 'фармасі', 'farmasi', 'farmassi',
        'продать', 'купить', 'покупать', 'вкладывать', 'деньги'
    ];

    private $curStage = '';

    private $myStages = [
        'hello' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Привет! Отличный профиль',
                'Привет, как дела?',
                'Привет! Отличный профиль.:)'
            ]
        ],
        'helloOfer' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Добрый день. Предлагаю работу в Instagram. Интересно?',
                'Добрый день. Предлагаю работу в Инстаграм. Интересно?',
                'Доброго времени суток. Предлагаю работу в Инстаграм. Интересно?',
                'Добрый день! Предлагаю работу в Instagram. Интересно?'
            ]
        ],
        'simpleOfer' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Предлагаю работу в Instagram. Интересно?',
                'Предлагаю работу в Инстаграм. Интересно?'
            ]
        ],
        'viberOfer' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Смотрите, объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество, где изложены все подробности работы. Самостоятельно все сможете изучить'
            ],
            'myShortMsg' => 'Объяснять всю суть в переписке долго. Оставьте ваш номер телефона и я добавлю вас в Вайбер сообщество'
        ],
        'oriQuestion' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'
            ]
        ],
        'phone' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Я сейчас вас добавлю в сообщество. Внимательно прочитайте приветственное сообщение. Потом нажмите на строчку вверху закрепленную и вас перенесёт в самое начало информации.'
            ]
        ]
    ];

    public function getAnswer(array $messages)
    {
        $result = [
            'status' => '',
            'txt' => '',
            'phone' => ''
        ];

        $lastMsgIndex = count($messages) - 1;
        $totalMessages = count($messages);

//        ["isMy" => true, "text" => ""]

        foreach ($messages as $num => $msg) {
            $messages[$num]['text'] = mb_strtolower(trim($msg['text']));
        }

        foreach ($messages as $num => $msg) {
            if ($msg['isMy']) {
                if ($this->isHelloStage($msg['text'])) {
                    $this->curStage = 'hello';
                    $this->myStages['hello']['isDone'] = true;
                    $this->myStages['hello']['messageIdex'] = $num;
                } else if ($this->isHelloOferStage($msg['text'])) {
                    $this->curStage = 'helloOfer';
                    $this->myStages['helloOfer']['isDone'] = true;
                    $this->myStages['helloOfer']['messageIdex'] = $num;

                    $this->myStages['hello']['isDone'] = true;
                    $this->myStages['hello']['messageIdex'] = $num;

                    $this->myStages['simpleOfer']['isDone'] = true;
                    $this->myStages['simpleOfer']['messageIdex'] = $num;
                } else if ($this->isSimpleOferStage($msg['text'])) {
                    $this->curStage = 'simpleOfer';
                    $this->myStages['helloOfer']['isDone'] = true;
                    $this->myStages['helloOfer']['messageIdex'] = $num;

                    $this->myStages['hello']['isDone'] = true;
                    $this->myStages['hello']['messageIdex'] = $num;

                    $this->myStages['simpleOfer']['isDone'] = true;
                    $this->myStages['simpleOfer']['messageIdex'] = $num;
                } else if ($this->isViberOferStage($msg['text'])) {
                    $this->curStage = 'viberOfer';
                    $this->myStages['viberOfer']['isDone'] = true;
                    $this->myStages['viberOfer']['messageIdex'] = $num;
                } else if ($this->isOriQuestionStage($msg['text'])) {
                    $this->curStage = 'oriQuestion';
                    $this->myStages['oriQuestion']['isDone'] = true;
                    $this->myStages['oriQuestion']['messageIdex'] = $num;
                }
            }
        }

//        dd($this->myStages);
        if (!$this->myStages['hello']['isDone'] and $totalMessages > 0) {
            $result['status'] = self::STATUS_WAITING_ANSWER;
            $result['txt'] = $this->myStages['helloOfer']['myMessages'][rand(0,3)];

            return $result;
        }
//dd($this->curStage, $this->myStages[$this->curStage], $lastMsgIndex);
        if ($this->myStages[$this->curStage]['messageIdex'] == $lastMsgIndex) {
            $result['status'] = self::STATUS_WAITING_ANSWER;

            return $result;
        }

        if ($this->myStages['viberOfer']['isDone']
            and $this->myStages['viberOfer']['messageIdex'] < $lastMsgIndex) {

            foreach($messages as $num => $msg) {
                if (!$msg['isMy'] and $this->isPhoneNumber($msg['text'])) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    $result['phone'] = $msg['text'];
                    break;
                }
            }

            $result['status'] = self::STATUS_DIALOG_FINISHED;

            return $result;
        }

        $totalAnswer = [];

        foreach($messages as $num => $msg) {
            if ($num > $this->myStages[$this->curStage]['messageIdex'] and !$msg['isMy']) {
                $totalAnswer[] = $msg['text'];
            }
        }

        $totalAnswer = implode(' ', $totalAnswer);

        switch ($this->curStage) {
            case 'simpleOfer':
            case 'helloOfer':
                if ($this->strposa($totalAnswer, $this->negativeAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposa($totalAnswer, $this->positiveAnswers)
                    or $this->strposa($totalAnswer, $this->viberOferQuestions)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                if ($this->strposa($totalAnswer, $this->oriQuestions)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $result['txt'] = $this->myStages['oriQuestion']['myMessages'][0];
                    break;
                }
                break;
            case 'hello':
                $result['status'] = self::STATUS_WAITING_ANSWER;
                $result['txt'] = $this->myStages['simpleOfer']['myMessages'][rand(0,1)];
                break;
            case 'viberOfer':
                if ($this->isPhoneNumber($totalAnswer)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    $result['phone'] = $totalAnswer;
                    break;
                }

                if ($this->strposa($totalAnswer, $this->negativeAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposa($totalAnswer, $this->positiveAnswers)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $result['txt'] = 'Ок, я жду номер';
                    break;
                }

                break;
            case 'oriQuestion':
                if ($this->strposa($totalAnswer, $this->negativeAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposa($totalAnswer, $this->positiveAnswers)
                    or $this->strposa($totalAnswer, $this->viberOferQuestions)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                break;
        }

        return $result;
    }

    private function isPhoneNumber($text)
    {
        $res = preg_replace("/[^0-9]/", '', $text);

        return (strlen($res) > 6);
    }

    private function isHelloStage($text) {
        return $this->strposa($text, $this->myStages['hello']['myMessages']);
    }
    private function isHelloOferStage($text) {
        return $this->strposa($text, $this->myStages['helloOfer']['myMessages']);
    }
    private function isViberOferStage($text) {
        return $this->strposa($text, $this->myStages['viberOfer']['myShortMsg']);
    }
    private function isOriQuestionStage($text) {
        return $this->strposa($text, $this->myStages['oriQuestion']['myMessages']);
    }
    private function isSimpleOferStage($text) {
        return $this->strposa($text, $this->myStages['simpleOfer']['myMessages']);
    }

    private  function strposa($haystack, $needle, $offset=0) {
        if(!is_array($needle)) $needle = array($needle);

        foreach($needle as $query) {
            $query = mb_strtolower($query);
            if(strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }

        return false;
    }
}