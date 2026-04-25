import { startStimulusApp } from "@symfony/stimulus-bundle";
import CareTaskController from "./controllers/care_task_controller.js";

const app = startStimulusApp();

app.register("care-task", CareTaskController);
