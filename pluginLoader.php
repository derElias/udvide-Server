<?php
require_once 'vendor/autoload.php';
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.06.2017
 * Time: 14:06
 */
class pluginLoader extends udvidePlugin
{
    private $singleton;
    /** @var array of strings */
    private $loadedPlugins = [];
    /** @var array of all active udvidePlugins */
    private $plugins = [];
    private $abouts = []; // todo make the about stuff class properties

    public function __construct()
    {
        udvidePlugin::__construct();

        $directory = '/plugins';
        $exclude = ['..', '.', 'udvidePlugin.php', 'udvidePluginAbout.php']; // Technically unnessecary (excluded by if(file_exists)), but more obvious
        $pluginNames = array_diff(scandir($directory), $exclude);

        $this->autoLoadPlugins($pluginNames);
    }

    private function autoLoadPlugins(array $pluginNames) {
        foreach ($pluginNames as $pluginName)
            $this->autoLoadPluginIfNotLoaded($pluginName);
    }

    private function autoLoadPluginIfNotLoaded($pluginName)
    {
        $isAlreadyLoaded = in_array($pluginName,$this->loadedPlugins);
        if (!$isAlreadyLoaded) {
            $this->autoLoadPluginIfValid($pluginName);
        }
    }

    private function autoLoadPluginIfValid($pluginName) {
        $isValidPlugin = file_exists('/plugins/'.$pluginName.'.php')
            && class_exists($pluginName)
            && is_subclass_of($pluginName,'udvidePlugin');

        if ($isValidPlugin) {
            $this->autoLoadPlugin($pluginName);
        }
    }

    private function autoLoadPlugin($pluginName)
    {
        $cPlugin = new $pluginName; // Ty composer autoloader

        if ($cPlugin instanceof udvidePlugin) { // PhpStorm apparently doesn't evaluate is_subclass_of
            // find out if the dependencies are already included
            $about = $cPlugin->aboutMe();
            $deps = $about->dependencies;

            foreach ($deps as $dep) {
                $this->autoLoadPluginIfNotLoaded($dep);
            }

            $this->abouts[] = $about;
            $this->plugins[] = $cPlugin;
            $this->loadedPlugins[] = $pluginName;
        }
    }

    //<editor-fold desc="Target">
    /**
     * Your code to modify the target before creation
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param target $target
     * @return boolean
     */
    public function onTargetCreate(target &$target): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onTargetCreate($target))
                return false;
        }
        return true;
    }

    /**
     * Your code to modify the target before change
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * $target are the values written to what was previously $subject
     * so make $target what you want in the end and $subject-s name what target should be deleted
     * return false and create a new target yourself, if you want to prevent the original from being deleted
     * @param target $target
     * @param target $subject
     * @return boolean
     */
    public function onTargetUpdate(target &$target, target &$subject): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onTargetUpdate($target))
                return false;
        }
        return true;
    }

    /**
     * Your code to modify the target before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param target $target
     * @return boolean
     */
    public function onTargetDelete(target &$target): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onTargetDelete($target))
                return false;
        }
        return true;
    }

    // public function onTargetRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>

    //<editor-fold desc="User">
    /**
     * Your code to modify the user before creation
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param user $user
     * @return boolean
     */
    public function onUserCreate(user &$user): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onUserCreate($user))
                return false;
        }
        return true;
    }

    /**
     * Your code to modify the user before change
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * $user are the values written to what was previously $subject
     * so make $user what you want in the end and $subject-s name what target should be deleted
     * return false and create a new user yourself, if you want to prevent the original from being deleted // todo docu example
     * @param user $user
     * @param user $subject
     * @return boolean
     */
    public function onUserUpdate(user &$user, user $subject): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onUserUpdate($user))
                return false;
        }
        return true;
    }

    /**
     * Your code to modify the user before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param user $user
     * @return boolean
     */
    public function onUserDelete(&$user): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onUserDelete($user))
                return false;
        }
        return true;
    }

    // public function onUserRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>

    //<editor-fold desc="Map">
    /**
     * Your code to modify the map before creation
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param map $map
     * @return boolean
     */
    public function onMapCreate(map &$map): bool {return true;}

    /**
     * Your code to modify the map before change
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * $map are the values written to what was previously $subject
     * so make $map what you want in the end and $subject-s name what target should be deleted
     * return false and create a new user yourself, if you want to prevent the original from being deleted // todo docu example
     * @param map $map
     * @param map $subject
     * @return boolean
     */
    public function onMapUpdate(map &$map, map &$subject): bool {return true;}

    /**
     * Your code to modify the map before deletion
     * return true as "go-ahead" and false to abort silently
     * throw a PluginException to indicate a problem to the user
     * @param map $map
     * @return bool
     */
    public function onMapDelete(map &$map): bool {return true;}

    // public function onUserRead(&$target); // stretch goal since todo objects aren't read as objects
    //</editor-fold>

    /**
     * Your code to modify a user before he logs in
     * only a valid User can be logged in
     * @param user $user
     * @return bool
     */
    public function onLogin(user &$user): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onLogin($user))
                return false;
        }
        return true;
    }

    /**
     * Your code to modify a editor-permission grant
     * @param user $user
     * @param target $target
     * @param $editor
     * @return bool
     */
    public function onEditorAssign(user &$user, target &$target, &$editor): bool{
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onEditorAssign($user, $target, $editor))
                return false;
        }
        return true;
    } // todo

    /**
     * Your code to modify a editor-permission revoke
     * @param user $user
     * @param target $target
     * @param $editor
     * @return bool
     */
    public function onEditorDivest(user &$user, target &$target, &$editor): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onEditorDivest($user, $target, $editor))
                return false;
        }
        return true;
    } // todo
    // public function onEditorRead(user &$user, target &$target):bool // todo stretch

    // public function onLog(&$log); // stretch goal since todo low reward

    /**
     * Your code to modify a Read Access from a mobile Client
     * @param target $target
     * @return bool
     */
    public function onMobileRead(target &$target): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onMobileRead($target))
                return false;
        }
        return true;
    } // the content of the Target is what is sent in the end

    public function onCustomTargetCreate(): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onCustomTargetCreate())
                return false;
        }
        return true;
    }

    /**
     * In case you need to setup something
     * @return bool
     */
    public function onSetup(): bool {
        foreach ($this->plugins as $plugin) {
            if (!$plugin->onSetup())
                return false;
        }
        return true;
    }

    /**
     * You have to tell us somethings about your Plugin - see udvidePluginAbout
     * @return udvidePluginAbout
     */
    public function aboutMe(): udvidePluginAbout
    {
        return new udvidePluginAbout(); // We can't modify loading behavior when we manage it in this class
    }
}