<?php
/**
 * Created by PhpStorm.
 * User: pata
 * Date: 04.01.19
 * Time: 1:16
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;


class BotController
{
    const STATUS_WAITING_ANSWER = 'waiting_answer';
    const STATUS_DIALOG_FINISHED = 'dialog_finished';

    public function __construct()
    {
    }

    private $positiveAnswers = [
        'интересно', 'интересует', 'давайте', 'давай', 'хорошо',  'трави', 'говори',
        'рассказывай', 'раскажите', 'раскажи', 'расскажи', 'игтересно', 'готов', 'готова', 'отлично',
        'yep', 'like', 'okey', 'okay', 'канешно', 'канечно', 'конечно', 'интиресно',
        'согласен', 'согласна', 'оке',  'слушаю', 'звичайно', 'очень', 'мене цікавить',
        'цікаво', 'готовий', 'добре', 'розповідай', 'розкажіть', 'розкажи', 'відмінно'
    ];

    private $positiveClearAnswers = ['да', 'ага', 'угу', 'так', 'yes','ok', 'da', 'ок'];
    private $negativeClearAnswers = ['нет',  'no', 'not','ні', 'нетт', 'неа'];

    private $negativeAnswers = [
        'не интересует', 'могу предложить', 'не интересно', 'уже есть', 'вже є', 'уже работаю', 'есть работа', 'нет спасибо', 'неинтересно', 'я работаю',
        'не цікаво',  'не потрібно', 'не цікавить', 'не цікаво', 'вже працюю', 'сотрудничаю', 'коллега', 'коллеги',
        'вжє робота', 'мене е робота', 'ні дякую', 'нецікаво', 'сама ищу', 'сам ищу',  'клуб', 'не хочу', 'уже в',
        'nope', 'no thanks', 'no need', 'уже с вами', 'работаем уже', 'уже работаем', 'идеального', 'колега', 'колеги'
    ];

    private $viberOferQuestions = [
        'поподробнее', 'что за работа', 'что за робота', 'что делать', 'расскажите', 'суть', 'условия',
        'какую', 'как', 'возможно', 'о чем', 'подробнее', 'раскажи', 'расскажи', 'именно', 'деталей', 'удивите меня',
        'имено', 'ват', 'подробности', 'подробно', 'нужно делать', 'нужна делать', 'что', 'заключается', 'узнать',
        'детальніше', 'що за робота', 'що за работа', 'що робити', 'що робити', 'що потрібно', 'вид деятельности',
        'що треба', 'розкажіть', 'роскажіть', 'умови', 'яку', 'як', 'можливо', 'про що', 'про шо', 'робота',
        'детальніше', 'саме', 'подробиці', 'потрібно робити', 'що', '?', '??', '???', 'в чому полягає робота',
        'чем заниматься', 'чем заниматся', 'какое направление', 'конкретніше', 'конкретнее', 'чем связана'
    ];

    private $oriClearAnswers = ['ори', 'орі', 'ori', 'оря'];
    private $oriQuestions = [
        'орифлейм', 'сетевой', 'продажи', 'реклама', 'маркетинг', 'эйвон', 'джерелия',
        'продавать', 'рекламировать', 'фаберлик', 'косметика', 'oriflame', 'faberlic',
        'jerelia', 'джерелія', 'оріфлейм', 'фаберлік', 'фармаси', 'фармасі', 'farmasi', 'farmassi',
        'продать', 'купить', 'покупать', 'вкладывать', 'деньги', 'компания', 'кампания', 'компанія'
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
        'extendedExplain' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Давать людям информацию в соц сетях. Это в двух словах, более подробно в Вайбер сообществе.'
            ],
            'myShortMsg' => 'Давать людям информацию в соц сетях'
        ],
        'oriQuestion' => [
            'isDone' => false,
            'messageIdex' => -1,
            'myMessages' => [
                'Это Орифлейм. Но это не продажи. Помимо продавцов в компании есть менеджеры, которые всем этим процессом управляют. Вот я, например, не продавец. Я менеджер и занимаюсь набором персонала, который будет помогать мне развивать нашу команду. Работа полностью онлайн. Я всему обучаю.'
            ]
        ]
    ];

    public function getAnswer(array $messages)
    {
        $this->curStage = '';
        $result = ['status' => '', 'txt' => '', 'phone' => '', 'ori' => false];

        if (count($messages) == 0) {
            $result['status'] = self::STATUS_WAITING_ANSWER;
            return $result;
        }

        $lastMsgIndex = count($messages) - 1;
        $totalMessages = count($messages);
        $myMessagesCount = 0;
        $hasOtherMessages = false;
        $isMyMessageLast = false;

        foreach ($messages as $num => $msg) {
            $messages[$num]['text'] = mb_strtolower(trim($msg['text']));
        }

        foreach ($messages as $num => $msg) {
            if ($msg['isMy']) {
                $isMyMessageLast = true;
                $myMessagesCount++;

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
                } else if ($this->isExtendedStage($msg['text'])) {
                    $this->curStage = 'extendedExplain';
                    $this->myStages['extendedExplain']['isDone'] = true;
                    $this->myStages['extendedExplain']['messageIdex'] = $num;
                } else {
                    $hasOtherMessages = true;
                }
            } else {
                $isMyMessageLast = false;
            }
        }

        foreach($messages as $num => $msg) {
            if (!$msg['isMy'] and $this->isPhoneNumber($msg['text'])) {
                $result['status'] = self::STATUS_DIALOG_FINISHED;

                if (mb_strlen($msg['text']) < 100) {
                    $result['phone'] = $msg['text'];
                }

                return $result;
            }
        }

        if ($hasOtherMessages or !array_key_exists($this->curStage, $this->myStages)) {

            $result['status'] = self::STATUS_DIALOG_FINISHED;

            return $result;
        }
        if (!$hasOtherMessages and $isMyMessageLast) {
            $result['status'] = self::STATUS_WAITING_ANSWER;

            return $result;
        }

        $totalAnswer = [];

        foreach($messages as $num => $msg) {
            if ($num > $this->myStages[$this->curStage]['messageIdex'] and !$msg['isMy']) {
                $totalAnswer[] = $msg['text'];
            }
        }

        $totalAnswer = implode(' ', $totalAnswer);

        if ($this->myStages['viberOfer']['isDone'] and !$this->myStages['oriQuestion']['isDone']
            and $this->myStages['viberOfer']['messageIdex'] < $lastMsgIndex and $myMessagesCount > 0) {

            $result['status'] = self::STATUS_WAITING_ANSWER;

            if ($myMessagesCount > 4 OR $hasOtherMessages) {
                $result['status'] = self::STATUS_DIALOG_FINISHED;
                return $result;
            } else if (!$this->myStages['oriQuestion']['isDone']) {
                if ($this->strposa($totalAnswer, $this->negativeAnswers)
                    or $this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    return $result;
                }
                if ($this->strposaExact($totalAnswer, $this->oriClearAnswers)
                    or $this->strposa($totalAnswer, $this->oriQuestions)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    $result['ori'] = true;
                    return $result;
                }
                if ($this->strposa($totalAnswer, $this->positiveAnswers)
                    or $this->strposaExact($totalAnswer, $this->positiveClearAnswers)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    return $result;
                }
                if ($this->strposa($totalAnswer, $this->viberOferQuestions)
                    and !$this->myStages['extendedExplain']['isDone']) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;

                    $this->checkDouble($messages, $this->myStages['extendedExplain']['myMessages'][0]);
                    $result['txt'] = $this->myStages['extendedExplain']['myMessages'][0];
                    return $result;
                }

                $result['status'] = '';
                return $result;
            }

            return $result;
        } else if ($this->myStages['oriQuestion']['isDone'] and !$this->myStages['viberOfer']['isDone']
            and $this->myStages['oriQuestion']['messageIdex'] < $lastMsgIndex and $myMessagesCount > 0) {

            if ($this->strposa($totalAnswer, $this->negativeAnswers)
                or $this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                $result['status'] = self::STATUS_DIALOG_FINISHED;
                return $result;
            }
            if ($this->strposa($totalAnswer, $this->positiveAnswers)
                or $this->strposaExact($totalAnswer, $this->positiveClearAnswers)) {
                $result['status'] = self::STATUS_WAITING_ANSWER;
                return $result;
            }
            if ($this->strposa($totalAnswer, $this->viberOferQuestions)
                and !$this->myStages['extendedExplain']['isDone']) {
                $result['status'] = self::STATUS_WAITING_ANSWER;

                $this->checkDouble($messages, $this->myStages['extendedExplain']['myMessages'][0]);
                $result['txt'] = $this->myStages['extendedExplain']['myMessages'][0];
                return $result;
            }

            $result['status'] = '';
            return $result;
        } else if ($this->myStages['oriQuestion']['isDone'] and $this->myStages['viberOfer']['isDone']) {
            if ($this->strposa($totalAnswer, $this->negativeAnswers)
                or $this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                $result['status'] = self::STATUS_DIALOG_FINISHED;
                return $result;
            }
            if ($this->strposa($totalAnswer, $this->positiveAnswers)
                or $this->strposaExact($totalAnswer, $this->positiveClearAnswers)) {
                $result['status'] = self::STATUS_WAITING_ANSWER;
                return $result;
            }
            if ($this->strposa($totalAnswer, $this->viberOferQuestions)
                and !$this->myStages['extendedExplain']['isDone']) {
                $result['status'] = self::STATUS_WAITING_ANSWER;

                $this->checkDouble($messages, $this->myStages['extendedExplain']['myMessages'][0]);
                $result['txt'] = $this->myStages['extendedExplain']['myMessages'][0];
                return $result;
            }

            $result['status'] = '';
            return $result;
        }

        if ($myMessagesCount > 4 OR $hasOtherMessages) {
            $result['status'] = self::STATUS_DIALOG_FINISHED;
            return $result;
        }

        if (!$this->myStages['hello']['isDone'] and $totalMessages > 3 and $myMessagesCount > 0) {
            $result['status'] = self::STATUS_DIALOG_FINISHED;

            return $result;
        } else if (!$this->myStages['hello']['isDone'] and $totalMessages > 0 and $myMessagesCount > 0) {
            $result['status'] = self::STATUS_WAITING_ANSWER;
            $this->checkDouble($messages, $this->myStages['helloOfer']['myMessages'][0]);
            $result['txt'] = $this->myStages['helloOfer']['myMessages'][0];

            return $result;
        }

        if ($this->myStages[$this->curStage]['messageIdex'] == $lastMsgIndex and $myMessagesCount > 0) {
            $result['status'] = self::STATUS_WAITING_ANSWER;

            return $result;
        }

        switch ($this->curStage) {
            case 'simpleOfer':
            case 'helloOfer':

                if ($this->strposa($totalAnswer, $this->negativeAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposaExact($totalAnswer, $this->oriClearAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    $result['ori'] = true;

                    break;
                }

                if ($this->strposa($totalAnswer, $this->oriQuestions)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    $result['ori'] = true;
                    break;
                }


                if ($this->strposa($totalAnswer, $this->positiveAnswers)
                    or $this->strposa($totalAnswer, $this->viberOferQuestions)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $this->checkDouble($messages, $this->myStages['viberOfer']['myMessages'][0]);
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                if ($this->strposaExact($totalAnswer, $this->positiveClearAnswers)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $this->checkDouble($messages, $this->myStages['viberOfer']['myMessages'][0]);
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                $result['status'] = self::STATUS_WAITING_ANSWER;
                break;
            case 'hello':
                $result['status'] = self::STATUS_WAITING_ANSWER;
                $this->checkDouble($messages, $this->myStages['simpleOfer']['myMessages'][0]);
                $result['txt'] = $this->myStages['simpleOfer']['myMessages'][0];
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

                if ($this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                $result['status'] = self::STATUS_WAITING_ANSWER;

                break;
            case 'oriQuestion':
                if ($this->strposa($totalAnswer, $this->negativeAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposaExact($totalAnswer, $this->negativeClearAnswers)) {
                    $result['status'] = self::STATUS_DIALOG_FINISHED;
                    break;
                }

                if ($this->strposa($totalAnswer, $this->positiveAnswers)
                    or $this->strposa($totalAnswer, $this->viberOferQuestions)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $this->checkDouble($messages, $this->myStages['viberOfer']['myMessages'][0]);
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                if ($this->strposaExact($totalAnswer, $this->positiveClearAnswers)) {
                    $result['status'] = self::STATUS_WAITING_ANSWER;
                    $this->checkDouble($messages, $this->myStages['viberOfer']['myMessages'][0]);
                    $result['txt'] = $this->myStages['viberOfer']['myMessages'][0];
                    break;
                }

                $result['status'] = self::STATUS_WAITING_ANSWER;
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
    private function isExtendedStage($text) {
        return $this->strposa($text, $this->myStages['extendedExplain']['myShortMsg']);
    }
    private function isSimpleOferStage($text) {
        return $this->strposa($text, $this->myStages['simpleOfer']['myMessages']);
    }

    public function strposa($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);

        $notWord = [".",",",";",":","!","@","#","$","%","^","&","*",
            "(",")","[","]","-","=","+","_","``","\n","\r","№","\\","/","|", " "];

        foreach($needle as $query) {
            $query = mb_strtolower($query);

            $begin = mb_stripos($haystack, $query);

//            echo '<pre>';
//            var_dump($query, $begin);
//            echo '</pre>';

//dd($begin);
            if($begin !== false) {
                $len = mb_strlen($query);

//                var_dump($len);

                if ($begin > 0) {
                    $prevChar = mb_substr($haystack, $begin-1, 1);

//                    echo '<pre>';
//                    var_dump($begin, $prevChar, $haystack, $query);
//                    echo '</pre>';

                    if (in_array($prevChar, $notWord)) {
//                        var_dump('ok');
                        return true;
                    } else {
//                        var_dump('error');
                        continue;
                    }
                }

                return true;
            }
        }

        return false;
    }

    private function checkDouble($messages, $txt) {
        foreach($messages as $msg) {
            if ($msg['isMy'] and strpos($msg['text'], $txt) !== false) {
                throw new \Exception('double');
            }
        }
    }

    private  function strposaExact($haystack, $needle) {
        if(!is_array($needle)) $needle = array($needle);

        foreach($needle as $query) {
            $query = mb_strtolower($query);

            $res = str_replace([".",",",";",":","!","@","#","$","%","^","&","*",
                "(",")","[","]","-","=","+","_","``","\n","\r","№","\\","/","|", " ", "?"], '`', $haystack);

            $res = explode('`', $res);

            foreach($res as $i => $row) {
                if ($row != '') {
                    if ($query == $row) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}