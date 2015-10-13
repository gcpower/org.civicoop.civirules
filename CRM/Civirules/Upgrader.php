<?php

/**
 * Copyright (C) 2015 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to CiviCRM under the AGPL-3.0
 */
class CRM_Civirules_Upgrader extends CRM_Civirules_Upgrader_Base {

  /**
   * Create CiviRules tables on extension install. Do not change the
   * sequence as there will be dependencies in the foreign keys
   */
  public function install() {
    $this->executeSqlFile('sql/createCiviruleAction.sql');
    $this->executeSqlFile('sql/createCiviruleCondition.sql');
    $this->executeSqlFile('sql/createCiviruleTrigger.sql');
    $this->executeSqlFile('sql/insertCiviruleTrigger.sql');
    $this->executeSqlFile('sql/createCiviruleRule.sql');
    $this->executeSqlFile('sql/createCiviruleRuleAction.sql');
    $this->executeSqlFile('sql/createCiviruleRuleCondition.sql');
    $this->executeSqlFile('sql/createCiviruleRuleLog.sql');
  }

  public function uninstall() {
    $this->executeSqlFile('sql/uninstall.sql');
  }

  public function upgrade_1001() {
    CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule` ADD event_params TEXT NULL AFTER event_id");
    if (CRM_Core_DAO::checkTableExists("civicrm_event")) {
      CRM_Core_DAO::executeQuery("
        INSERT INTO civirule_event (name, label, object_name, op, cron, class_name, created_date, created_user_id)
        VALUES
          ('groupmembership', 'Daily trigger for group members', NULL, NULL, 1, 'CRM_CivirulesCronTrigger_GroupMembership',  CURDATE(), 1);
        ");
    }
    return true;
  }
  /**
   * Method for upgrade 1002
   * (rename events to trigger, check https://github.com/CiviCooP/org.civicoop.civirules/issues/42)
   * - rename table civirule_event to civirule_trigger
   * - rename columns event_id, event_params in table civirule_rule to trigger_id, trigger_params
   * - remove index on event_id
   * - add index on trigger_id
   */
  public function upgrade_1002() {
    // rename table civirule_event to civirule_trigger
    if (CRM_Core_DAO::checkTableExists("civirule_event")) {
      CRM_Core_DAO::executeQuery("RENAME TABLE civirule_event TO civirule_trigger");
    } else {
      $this->executeSqlFile('sql/createCiviruleTrigger.sql');
      $this->executeSqlFile('sql/insertCiviruleTrigger.sql');
    }
    // rename columns event_id and event_params in civirule_rule
    if (CRM_Core_DAO::checkTableExists("civirule_rule")) {
      $this->executeSqlFile('sql/upgrade1002.sql');
    }
    return true;
  }
}