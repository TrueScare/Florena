import { startStimulusApp } from "@symfony/stimulus-bundle";
import CareTaskController from "./controllers/care_task_controller.js";

import NotificationsController from "./controllers/notifications_controller.js";
import MobileNavController from "./controllers/mobile_nav_controller.js";
import PlantLocationSuggestionsController from "./controllers/plant_location_suggestions_controller.js";

const app = startStimulusApp();

app.register("care-task", CareTaskController);

app.register("notifications", NotificationsController);
app.register("mobile-nav", MobileNavController);
app.register("plant-location-suggestions", PlantLocationSuggestionsController);
