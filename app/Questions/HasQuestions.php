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
            $question->nonInteractive($nonInteractive);
            $answers += [
                $question->flag => $question->resolveAnswer($this),
            ];
        }

        return $answers;
    }
}
