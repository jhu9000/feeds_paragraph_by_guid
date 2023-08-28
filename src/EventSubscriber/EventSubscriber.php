<?php

namespace Drupal\feeds_paragraph_by_guid\EventSubscriber;

use Drupal\feeds\Event\EntityEvent;
use Drupal\feeds\Event\FeedsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Feeds event subscriber.
 */
class EventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[FeedsEvents::PROCESS_ENTITY_PREVALIDATE][] = 'entityPrevalidate';
    return $events;
  }

  /**
   * Callback for feeds entity prevalidate event.
   *
   * @param Drupal\feeds\Event\EntityEvent $event
   */
  public function entityPrevalidate(EntityEvent $event) {
    $entity = $event->getEntity();
    $guid = @$entity->feeds_item[0]->guid;
    if ($guid) {
      /** \Drupal\Core\Field\FieldDefinitionInterface */
      $fields = $entity->getFieldDefinitions($entity_type, $bundle);
      foreach ($fields as $name => $field) {
        /** \Drupal\Core\Field\FieldStorageDefinitionInterface */
        $field_storage = $field->getFieldStorageDefinition();
        $type = $field_storage->getType();
        $target_type = $field_storage->getSetting('target_type');
        if ($type == 'entity_reference_revisions' && $target_type == 'paragraph') {
          $database = \Drupal::database();
          $query = $database->query("
            SELECT feeds_item_guid, entity_id, revision_id
            FROM {paragraph__feeds_item}
            WHERE feeds_item_guid LIKE :feeds_item_guid
            ORDER BY feeds_item_guid ASC
          ", [
            ':feeds_item_guid' => $guid.'-'.$name.'-%',
          ]);
          $result = $query->fetchAll();
          if ($result) {
            $items = [];
            foreach ($result as $record) {
              // The db query above is written to avoid using database specific
              // syntax and will return results for all descendents. This regex
              // filters for only the direct children.
              if (preg_match('/^'.$guid.'-'.$name.'-([0-9])+$/', $record->feeds_item_guid, $matches)) {
                $delta = $matches[1];
                $items[$delta] = [
                  'target_id' => $record->entity_id,
                  'target_revision_id' => $record->revision_id
                ];
              }
            }
            if (!empty($items)) {
              ksort($items);
              $entity->set($name, $items);
            }
          }
        }
      }
    }
  }

}