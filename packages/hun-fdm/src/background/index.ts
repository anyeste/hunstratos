import { createPlugin } from "@skyvexsoftware/stratos-sdk/helpers";

export default createPlugin({
  async onStart(ctx) {
    ctx.logger.info("Background", "Plugin started");
  },
  async onStop(ctx) {
    ctx.logger.info("Background", "Plugin stopped");
  },
});
