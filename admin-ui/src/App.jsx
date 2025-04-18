import React from "react";
import { Container, Typography, Button, Box, Paper, AppBar, Toolbar, CircularProgress } from "@mui/material";

function App() {
  // Platzhalter für API-Integration
  const [syncing, setSyncing] = React.useState(false);
  const [logs, setLogs] = React.useState(["System bereit."]);
  const [gitStatus, setGitStatus] = React.useState("Clean");

  const handleSync = async () => {
    setSyncing(true);
    setLogs((l) => ["Synchronisation gestartet...", ...l]);
    // API-Aufruf an Backend (z.B. /api/sync) simulieren
    setTimeout(() => {
      setSyncing(false);
      setLogs((l) => ["Sync erfolgreich!", ...l]);
      setGitStatus("1 Änderung (Auto-Sync)");
    }, 2000);
  };

  return (
    <Box sx={{ flexGrow: 1, bgcolor: "#f6f6f6", minHeight: "100vh" }}>
      <AppBar position="static">
        <Toolbar>
          <Typography variant="h6" sx={{ flexGrow: 1 }}>
            WordPress Staging Admin
          </Typography>
          <Button color="inherit" onClick={handleSync} disabled={syncing}>
            {syncing ? <CircularProgress size={20} color="inherit" /> : "Sync jetzt"}
          </Button>
        </Toolbar>
      </AppBar>
      <Container maxWidth="md" sx={{ mt: 4 }}>
        <Paper elevation={2} sx={{ p: 3, mb: 2 }}>
          <Typography variant="h5" gutterBottom>
            Staging-Status
          </Typography>
          <Typography>Git-Status: <b>{gitStatus}</b></Typography>
        </Paper>
        <Paper elevation={2} sx={{ p: 3 }}>
          <Typography variant="h6" gutterBottom>
            Logs
          </Typography>
          <Box sx={{ maxHeight: 200, overflow: "auto", bgcolor: "#222", color: "#0f0", fontFamily: "monospace", p: 2 }}>
            {logs.map((log, i) => (
              <div key={i}>{log}</div>
            ))}
          </Box>
        </Paper>
      </Container>
    </Box>
  );
}

export default App;
