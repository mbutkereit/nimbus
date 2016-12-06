<?php

namespace Drupal\nimbus\Service\Question;

use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class QuestionFactory.
 *
 * @package Drupal\nimbus\Service\Question
 */
class QuestionFactory implements QuestionFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function createConfirmationQuestion($question, $default = TRUE, $trueAnswerRegex = '/^y/i') {
    return new ConfirmationQuestion($question, $default, $trueAnswerRegex);
  }

}
