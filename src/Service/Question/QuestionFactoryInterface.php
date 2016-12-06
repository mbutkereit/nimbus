<?php

namespace Drupal\nimbus\Service\Question;

/**
 * Interface QuestionFactoryInterface.
 *
 * @package Drupal\nimbus\Service\Question
 */
interface QuestionFactoryInterface {

  /**
   * Creates a new confirmation question object.
   *
   * @param string $question
   *    The question to ask to the user.
   * @param bool $default
   *    The default answer.
   * @param string $trueAnswerRegex
   *    The regex string of the possibilities for the user to answer.
   *
   * @return \Symfony\Component\Console\Question\ConfirmationQuestion
   *    The confirmation question object.
   */
  public function createConfirmationQuestion($question, $default = TRUE, $trueAnswerRegex = '/^y/i');

}
