<?php

namespace Drupal\custom_publishing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures custom_publishing settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_publishing_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_publishing.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('custom_publishing.settings');

    $form['notify'] = array(
      '#title' => $this->t('Enable email notifications'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('notify') ? 1 : 0,
      '#description' => $this->t('Enable notifications for anonymously-submitted nodes.'),
      '#required' => FALSE,
    );

    $form['notify_address'] = array(
      '#title' => $this->t('Email notification address'),
      '#type' => 'email',
      '#default_value' => $config->get('notify_address') ? $config->get('notify_address') : '',
      '#description' => $this->t('Must be a valid email address.'),
      '#required' => FALSE,
    );

    $form['notify_subject'] = array(
      '#title' => $this->t('Email subject'),
      '#type' => 'textfield',
      '#default_value' => $config->get('notify_subject') ? $config->get('notify_subject') : '',
      '#description' => $this->t('Specify the email subject. Allowed tokens: [[title]], [[link]], [[created]], [[sitename]]'),
      '#required' => FALSE,
    );

    $form['notify_message'] = array(
      '#title' => $this->t('Email message body'),
      '#type' => 'textarea',
      '#default_value' => $config->get('notify_message') ? $config->get('notify_message') : '',
      '#description' => $this->t('Specify the email body. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
      '#required' => FALSE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    foreach ($values as $key => $value) {
      $this->config('custom_publishing.settings')
        ->set($key, $value)
        ->save();
    }

    parent::submitForm($form, $form_state);
  }

}
