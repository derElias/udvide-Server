<?php
include '../udvidePlugin.php';
/**
 * This Plugin was written by Simon
 * It can serve as reference for future Plugins
 * User: User
 * Date: 06.06.2017
 * Time: 14:04
 */
class fhwsApi extends udvidePlugin
{

    /**
     * You have to tell us somethings about your Plugin - see udvidePluginAbout
     * @return udvidePluginAbout
     */
    public function aboutMe(): udvidePluginAbout
    {
        return (new udvidePluginAbout())
            // we do not change how users are made (yet)
            ->setCustomUserOptions(false)
            ->setAdditionalUserOptions(false)
            ->setCustomTargetOptions([
                [
                    "id"=>"rooms",
                    "description"=>"Rooms and Floors Buildings as\n
                        Building: floor.last-room floor2.last-room",
                    "preFill"=>"\"I: 1.21 2.19 3.24;\nH: 0.5 1.11\")",
                    "type"=>LARGE_TEXT
                ]
            ])
            ->setAdditionalTargetOptions([
                [
                    "id"=>"RoomNbr",
                    "type"=>SMALL_TEXT
                ]
            ]);
    }

    public function onCustomTargetCreate(): bool
    {
        foreach ($this->userInput as $value) {
            if ($value["id"] == "rooms") {
                $this->handleRooms($value["content"]);
            }
        }
        return true;
    }

    private function handleRooms($roomIDs)
    {
        $buildings = explode("; ",$roomIDs);
        foreach ($buildings as $building) {
            $building = explode(":", $building);
            $buildingid = trim($building[0]);
            $floors = explode(" ",trim($building[1]));

            foreach ($floors as $floor) {
                $floor = explode(".",$floor);
                $floorid = trim($floor[0]);
                $roomCnt = (integer) $floor[1];

                $map = (new map())
                    ->setName("FHWS: $buildingid . "." . $floorid")
                    ->setImage($this->pluginData["mapImage"])
                    ->create();

                for ($roomid = 1; $roomid <= $roomCnt; $roomid++) {
                    $target = (new Target())
                        ->setName("FHWS: $buildingid.$floorid.$roomid")
                        ->setImage($this->pluginData["image"])
                        ->setContent()
                        ->setMap($map->getName())
                        ->setOwner(user::getLoggedInUser()->getUsername());
                }
            }
        }
    }

    public function onMobileRead(target &$target): bool
    {
        $content = $target->getContent();
        $content = str_replace("CURRENTUSER","todo",$content);
        $target->setContent($content);
        return true;
    }
}