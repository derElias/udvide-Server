<?php
require_once '../../vendor/autoload.php';
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
            // we do not change how users are made or added
            ->setCustomUserOptions(false)
            ->setAdditionalUserOptions(false)
            ->setCustomTargetOptions([
                [
                    "id"=>"rooms",
                    "description"=>"Rooms and Floors Buildings as\n
                        Building: floor.last-room floor2.last-room",
                    "preFill"=>"\"I: 1.21 2.19 3.24;\nH: 0.5 1.11\")",
                    "type"=>self::PLUGIN_INPUT_LARGE_TEXT
                ]
            ])
            ->setAdditionalTargetOptions([
                [
                    "id"=>"RoomNbr",
                    "description"=>"Room identifier",
                    "preFill"=>"z.B.: I.2.2",
                    "type"=>self::PLUGIN_INPUT_SMALL_TEXT
                ]
            ]);
    }

    public function onTargetCreate(target &$target): bool
    {
        if (!empty($this->userInput['RoomNbr'])) {
            $target->setPluginData('fhwsApi',$this->userInput['RoomNbr']);
        }
        return true;
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
                    $identifier = $buildingid.$floorid.$roomid;
                    $target = (new Target())
                        ->setName("FHWS: $identifier")
                        ->setPluginData('fhwsApi',['RoomNbr'=>$identifier])
                        ->setImage($this->pluginData["image"])
                        ->setContent('FHWS_API_01')
                        ->setMap($map->getName())
                        ->setOwner(user::getLoggedInUser()->getUsername());
                }
            }
        }
    }

    public function onMobileRead(target &$target): bool
    {
        $insert['01'] = "";
        $templ = file_get_contents('fhwsRoomTemplate.richtxt');
        $roomId = $this->getRoomInfo($target);
        $roomEvts = $this->getApiInfoForRoom($roomId);
        foreach ($roomEvts as $evt) {
            $roomStr = $templ;
            $roomStr = str_replace("#EVT_NAME#",$evt['name'],$roomStr);
            $roomStr = str_replace("#ROOM_NAME#",$roomId,$roomStr);
            foreach ($evt['lecturerView'] as $lect) {
                $lectFullName = $lect['title'] . ' ' . $lect['fistName'] . ' ' . $lect['lastName'];
                $roomStr = str_replace("#LECT#",$lectFullName . "#LECT#",$roomStr);
            }
            $roomStr = str_replace("#LECT#",'',$roomStr);
            $insert['01'] .= $roomStr;
        }

        $content = $target->getContent();
        $content = str_replace("FHWS_API_01",$insert['01'],$content);
        $target->setContent($content);
        return true;
    }

    private function getRoomInfo(target $target):string
    {
        return $target->getPluginData('fhwsApi')['RoomNbr'];
    }

    private function getApiInfoForRoom(string $identifier)
    {
        $apic = $this->getApiContent();
        $eventsHere = [];
        foreach ($apic as $evt) {
            foreach ($evt['roomsView'] as $room)
                if ($room['name'] == $identifier) {
                    $eventsHere[] = $evt;
            }
        }

    }

    private function getApiContent()
    {
        $defaultSize = 10;
        $defaultOffset = 0;

        $baseUrl = "https://apistaging.fiw.fhws.de/mo/api";
        $eventsTodayRequestUrl = "/events/today?&offset=$defaultOffset&size=$defaultSize";

        $request = (new HTTP_Request2())
            ->setUrl($baseUrl . $eventsTodayRequestUrl)
            ->setMethod(HTTP_Request2::METHOD_GET);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                return json_decode($response->getBody());
            } else {
                throw new PluginException('The FHWS API did not like our request and Responded with '
                    . $response->getStatus() . ' saying ' . $response->getReasonPhrase());
            }
        } catch (HTTP_Request2_Exception $e) {
            error_log('FHWS API: HTTP Request 2 to ' . $request->getUrl() . ' resulted in Exception: ' . $e->getMessage());
        }
        return "An Error occurred!";
    }
}
