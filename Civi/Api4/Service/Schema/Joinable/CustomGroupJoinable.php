<?php

/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 * $Id$
 *
 */


namespace Civi\Api4\Service\Schema\Joinable;

use Civi\Api4\CustomField;

class CustomGroupJoinable extends Joinable {

  /**
   * @var string
   */
  protected $joinSide = self::JOIN_SIDE_LEFT;

  /**
   * @var string
   *
   * Name of the custom field column.
   */
  protected $columns;

  /**
   * @param $targetTable
   * @param $alias
   * @param bool $isMultiRecord
   * @param string $entity
   * @param string $columns
   */
  public function __construct($targetTable, $alias, $isMultiRecord, $entity, $columns) {
    $this->entity = $entity;
    $this->columns = $columns;
    parent::__construct($targetTable, 'entity_id', $alias);
    $this->joinType = $isMultiRecord ?
      self::JOIN_TYPE_ONE_TO_MANY : self::JOIN_TYPE_ONE_TO_ONE;
  }

  /**
   * @inheritDoc
   */
  public function getEntityFields() {
    if (!$this->entityFields) {
      $fields = CustomField::get()
        ->setCheckPermissions(FALSE)
        ->setSelect(['custom_group.name', '*'])
        ->addWhere('custom_group.table_name', '=', $this->getTargetTable())
        ->execute();
      foreach ($fields as $field) {
        $this->entityFields[] = \Civi\Api4\Service\Spec\SpecFormatter::arrayToField($field, $this->getEntity());
      }
    }
    return $this->entityFields;
  }

  /**
   * @inheritDoc
   */
  public function getField($fieldName) {
    foreach ($this->getEntityFields() as $field) {
      $name = $field->getName();
      if ($name === $fieldName || strrpos($name, '.' . $fieldName) === strlen($name) - strlen($fieldName) - 1) {
        return $field;
      }
    }
    return NULL;
  }

  /**
   * @return string
   */
  public function getSqlColumn($fieldName) {
    if (strpos($fieldName, '.') !== FALSE) {
      $fieldName = substr($fieldName, 1 + strrpos($fieldName, '.'));
    }
    return $this->columns[$fieldName];
  }

}
