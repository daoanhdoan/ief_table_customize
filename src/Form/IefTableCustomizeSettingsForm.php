<?php

namespace Drupal\ief_table_customize\Form;

use Drupal\Core\Entity\Entity\EntityFormMode;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class IefTableCustomizeSettingsForm extends ConfigFormBase
{
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $config = $this->config('ief_table_customize.settings');

    $entity_types = [];
    foreach (\Drupal::entityTypeManager()->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type->get('field_ui_base_route') && $entity_type->hasFormClasses()) {
        $entity_types[$entity_type_id] = $entity_type->getLabel();
      }
    }
    $form['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => t('Enable IEF Table View Mode for selected Entity types'),
      '#options' => $entity_types,
      '#default_value' => $config->get('entity_types'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $config = \Drupal::configFactory()->getEditable('ief_table_customize.settings');
    $config->set('entity_types', $form_state->getValue('entity_types'));
    $config->save();

    foreach ($form_state->getValue('entity_types') as $item => $value) {
      if ($value) {
        $entity_form_mode = new EntityFormMode(['id' => $item . '.ief_table', 'label' => 'IEF Table', 'targetEntityType' => $item], 'entity_form_mode');
        $entity_form_mode->save();
        $entity_view_mode = new EntityViewMode(['id' => $item . '.ief_table', 'label' => 'IEF Table', 'targetEntityType' => $item], 'entity_view_mode');
        $entity_view_mode->save();
      }
      else {
        $entity_form_mode = \Drupal::entityTypeManager()->getStorage('entity_form_mode')->loadByProperties(['id' => "{$item}.ief_table"]);
        if(!empty($entity_form_mode["{$item}.ief_table"])) {
          $entity_form_mode["{$item}.ief_table"]->delete();
        }
        $entity_view_mode = \Drupal::entityTypeManager()->getStorage('entity_view_mode')->loadByProperties(['id' => "{$item}.ief_table"]);
        if(!empty($entity_view_mode["{$item}.ief_table"])) {
          $entity_view_mode["{$item}.ief_table"]->delete();
        }
      }
    }

    $this->messenger()->addStatus($this->t('All settings have been saved.'));
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames()
  {
    return ['ief_table_customize.settings'];
  }

  /**
   * @inheritDoc
   */
  public function getFormId()
  {
    return 'ief_table_customize_settings';
  }
}
