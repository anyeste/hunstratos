import { usePluginContext } from "@skyvexsoftware/stratos-sdk";
import "./styles.css";

export default function Plugin() {
  const { pluginId } = usePluginContext();

  return (
    <div className="p-6">
      <h1 className="text-xl font-semibold text-foreground">{pluginId}</h1>
      <p className="mt-2 text-sm text-text-muted">Hello from your Stratos plugin!</p>
    </div>
  );
}
