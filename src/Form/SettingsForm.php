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
   * * Fixed for PHP 8.4: Added explicit nullability '?' to the Request parameter.
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?Request $request = NULL) {
    $config = $this->config('custom_publishing.settings');

    $role_options = [];
    $roles = Role::loadMultiple();
    foreach ($roles as $role) {
      $role_options[$role->id()] = $role->label();
    }
    $form['roles'] = array(
      '#title' => $this->t('Roles'),
      '#type' => 'checkboxes',
      '#default_value' => $config->get('roles') ?? [], // Fallback for PHP 8 array safety
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
      '#default_value' => $config->get('types') ?? [], // Fallback for PHP 8 array safety
      '#description' => $this->t('Choose which content types to apply custom publishing.'),
      '#options' => $type_options,
    );

    $form['divider_1'] = array(
      '#markup' => '<h4>Staff confirmation email</h4>'
    );

    $form['notify'] = array(
      '#title' => $this->t('Enable staff email notifications'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('notify') ? 1 : 0,
      '#description' => $this->t('Enable notifications for custom publishing nodes.'),
      '#required' => FALSE,
    );

    $form['notify_address'] = array(
      '#title' => $this->t('Staff email notification address'),
      '#type' => 'email',
      '#default_value' => $config->get('notify_address') ?: '',
      '#description' => $this->t('Must be a valid email address.'),
      '#required' => FALSE,
    );

    $form['notify_subject'] = array(
      '#title' => $this->t('Staff email subject'),
      '#type' => 'textfield',
      '#default_value' => $config->get('notify_subject') ?: '',
      '#description' => $this->t('Specify the email subject. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
      '#required' => FALSE,
    );

    $form['notify_message'] = array(
      '#title' => $this->t('Staff email message body'),
      '#type' => 'textarea',
      '#default_value' => $config->get('notify_message') ?: '',
      '#description' => $this->t('Specify the email body. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
      '#required' => FALSE,
    );

    $form['divider_2'] = array(
      '#markup' => '<h4>User confirmation email</h4>'
    );

    $form['confirm_email_types'] = array(
      '#title' => $this->t('Enable user confirmation emails for these content types:'),
      '#type' => 'textfield',
      '#default_value' => $config->get('confirm_email_types') ?: '',
      '#description' => $this->t('Enter a comma-separated list of content type machine names that <strong>require email confirmation for emails listed in "field_contact_email"</strong> if present. These must be content types selected above in the "Content types" field.'),
      '#required' => FALSE,
    );

    $form['confirm_email_from_address'] = array(
      '#title' => $this->t('User confirmation email reply address'),
      '#type' => 'email',
      '#default_value' => $config->get('confirm_email_from_address') ?: '',
      '#description' => $this->t('Must be a valid email address.'),
      '#required' => FALSE,
    );

    $form['confirm_email_notify_subject'] = array(
      '#title' => $this->t('User confirmation email subject'),
      '#type' => 'textfield',
      '#default_value' => $config->get('confirm_email_notify_subject') ?: '',
      '#description' => $this->t('Specify the email subject. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
      '#required' => FALSE,
    );

    $form['confirm_email_notify_message'] = array(
      '#title' => $this->t('User confirmation email message body'),
      '#type' => 'textarea',
      '#default_value' => $config->get('confirm_email_notify_message') ?: '',
      '#description' => $this->t('Specify the email body. Allowed tokens: [[title]], [[link]], [[created]], [[type]], [[sitename]]'),
      '#required' => FALSE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $confirm_email_types = $form_state->getValue('confirm_email_types');
    $confirm_email_from_address = $form_state->getValue('confirm_email_from_address');
    $confirm_email_notify_subject = $form_state->getValue('confirm_email_notify_subject');
    $confirm_email_notify_message = $form_state->getValue('confirm_email_notify_message');

    $confirm_fields = [
      'confirm_email_types' => $confirm_email_types,
      'confirm_email_from_address' => $confirm_email_from_address,
      'confirm_email_notify_subject' => $confirm_email_notify_subject,
      'confirm_email_notify_message' => $confirm_email_notify_message,
    ];

    $filled_fields = array_filter($confirm_fields, function($value) {
      return !empty($value);
    });

    if (!empty($filled_fields) && count($filled_fields) < count($confirm_fields)) {
      $empty_fields = array_keys(array_diff_key($confirm_fields, $filled_fields));
      foreach ($empty_fields as $field_name) {
        $form_state->setErrorByName($field_name,
          $this->t('All confirmation email fields must be filled out if any one is filled.')
        );
      }
    }

    if (!empty($confirm_email_types)) {
      $confirm_types_array = array_map('trim', explode(',', $confirm_email_types));
      $selected_types = array_filter($form_state->getValue('types') ?? []);
      $all_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();

      foreach ($confirm_types_array as $type_machine_name) {
        if (empty($type_machine_name)) {
          continue;
        }

        if (!isset($all_types[$type_machine_name])) {
          $form_state->setErrorByName('confirm_email_types',
            $this->t('The content type "@type" does not exist.', ['@type' => $type_machine_name])
          );
        }
        elseif (!isset($selected_types[$type_machine_name])) {
          $form_state->setErrorByName('confirm_email_types',
            $this->t('The content type "@type" must be selected in the "Content types" field above.', ['@type' => $type_machine_name])
          );
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('custom_publishing.settings');
    $values = $form_state->getValues();

    // Clean up internal Drupal form values before saving config
    $skip_keys = ['submit', 'form_build_id', 'form_token', 'form_id', 'op'];
    
    foreach ($values as $key => $value) {
      if (!in_array($key, $skip_keys)) {
        $config->set($key, $value);
      }
    }
    
    $config->save();
    parent::submitForm($form, $form_state);
  }

}