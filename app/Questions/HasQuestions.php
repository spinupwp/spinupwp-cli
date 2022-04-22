<?php

namespace App\Questions;

trait HasQuestions
{
    /** @return array<Question> */
    public function questions(): array
    {
        return [];
    }

    public function askQuestions(bool $nonInteractive = false): array
    {
        $answers = [];
        foreach ($this->questions() as $question) {
            if ($question->skip) {
                continue;
            }

            $question->nonInteractive($nonInteractive);
            $answers += [
                $this->getAnswerKey($question) => $question->resolveAnswer($this),
            ];
        }

        return $answers;
    }

    private function getAnswerKey(Question $question): string
    {
        return !empty($question->flag) ? $question->flag : $question->key;
    }
}
