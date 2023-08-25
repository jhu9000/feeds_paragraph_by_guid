<?php

namespace Drupal\feeds_paragraph_by_guid\EventSubscriber;

use Drupal\feeds\Event\EntityEvent;
use Drupal\feeds\Event\FeedsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Modifies the parsed result of a feed.
 */
class EventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[FeedsEvents::PROCESS_ENTITY_PRESAVE][] = 'entityPresave';
    return $events;
  }

  /**
   * Finds paragraphs and sets them to node paragraph fields by matching the
   * node feeds item guid with the paragraph feeds item guid.
   *
   * @param Drupal\feeds\Event\EntityEvent $event
   */
  public function entityPresave(EntityEvent $event) {
    $entity = $event->getEntity();
    $entity_type = $entity->getEntityTypeId();
    $guid = @$entity->feeds_item[0]->guid;
    if ($guid && $entity_type == 'node') {
      $bundle = $entity->bundle();
      $database = \Drupal::database();
      $entity_field_manager = \Drupal::service('entity_field.manager');
      /** \Drupal\Core\Field\FieldDefinitionInterface */
      $fields = $entity_field_manager->getFieldDefinitions($entity_type, $bundle);
      foreach ($fields as $name => $field) {
        /** \Drupal\Core\Field\FieldStorageDefinitionInterface */
        $field_storage = $field->getFieldStorageDefinition();
        $type =  $field_storage->getType();
        $target_type = $field_storage->getSetting('target_type');
        if ($type == 'entity_reference_revisions' && $target_type == 'paragraph') {
          $query = $database->query("
            SELECT entity_id, revision_id
            FROM {paragraph__feeds_item}
            WHERE feeds_item_guid LIKE :feeds_item_guid
            ORDER BY feeds_item_guid ASC
          ", [
            ':feeds_item_guid' => $guid.'-'.$name.'-%',
          ]);
          $result = $query->fetchAll();
          if ($result) {
            $paragraphs = [];
            foreach ($result as $record) {
              $paragraphs[] = [
                'target_id' => $record->entity_id,
                'target_revision_id' => $record->revision_id
              ];
            }
            $entity->set($name, $paragraphs);
          }
        }
      }
    }
  }

}