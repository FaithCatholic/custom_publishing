<?php

namespace Drupal\custom_publishing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Symfony\Component\HttpFoundation\Request;

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

    $role_options = [];
    $roles = Role::loadMultiple();
    foreach ($roles as $role) {
      $role_options[$role->id()] = $role->label();
    }
    $form['roles'] = array(
      '#title' => $this->t('Roles'),
      '#type' => 'checkboxes',
      '#default_value' => $config->get('roles'),
      '#description' => $this->t('Choose roles to which custom publishing applies.'),
      '#options' => $role_options,
      '#required' => FALSE,
    );

    $type_options = [];
    $types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    foreach ($types as $key => $value) {
      $type_options[$key] = $value->get('name');
    }
    $form['types'] = array(
      '#title' => $this->t('Content types'),
      '#type' => 'checkboxes',
      '#default_value' => $config->get('types'),
      '#description' => $this->t('Choose which content types to apply custom publishing.'),
      '#options' => $type_options,
    );

    $form['notify'] = array(
      '#title' => $this->t('Enable email notifications'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('notify') ? 1 : 0,
      '#description' => $this->t('Enable notifications for custom publishing nodes.'),
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
      '#description' => $this->t('Specify the email subject. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
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
