<?php
namespace system;

use system\router_abs;

class router extends router_abs
{
  protected static function router()
  {
    $config = static::$currMod::loadConfig();
    if (!isset($config["defMod"])) {$config["defMod"]["value"] = "cms";}
    if (!isset(classload::$configCache[static::$modular])) {static::$modular = $config["defMod"]["value"];}
    static::$modular::router(static::$data);
  }
}
