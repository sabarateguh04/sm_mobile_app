# Jira Integration Module

Module untuk integrasi otomatis dengan Jira API, memungkinkan pembuatan dan management tiket secara programmatic.

## 📁 Struktur File

```
jira_integration/
├── __init__.py              # Package initialization
├── jira_config.py           # Konfigurasi dan konstanta Jira
├── jira_client.py           # Client functions untuk Jira API
├── create_tickets.py        # Script untuk create tickets FASE 1
└── README.md                # Dokumentasi ini
```

## 🔧 Setup

### 1. Environment Variables

Pastikan variabel berikut ada di file `.env`:

```env
JIRA_BASE_URL=https://estrada.atlassian.net
JIRA_EMAIL=your-email@company.com
JIRA_API_TOKEN=your-api-token
JIRA_PROJECT_KEY=DDP
JIRA_BOARD_ID=7
```

### 2. Install Dependencies

```bash
pip install requests python-dotenv
```

## 🚀 Usage

### Create Tickets untuk FASE 1

```bash
python -m jira_integration.create_tickets
```

Script ini akan:
1. Get active sprint dari Jira board
2. Create 10 story tickets (Backend, Frontend, Integration)
3. Create subtasks untuk setiap story
4. Assign tickets ke user yang configured
5. Link tickets ke Epic SM-BO
6. Set story points, due dates, dan priority

### Menggunakan Jira Client Functions

```python
from jira_integration.jira_client import (
    create_story, create_subtask, change_issue_status,
    get_active_sprint, update_story_points, post_comment
)

# Create story
story_key = create_story(
    summary="[BE] My Feature",
    description="Feature description",
    priority="High",
    story_points=5,
    start_date="2026-07-01",
    due_date="2026-07-05",
    epic_key="SM-BO",
    sprint_id=123
)

# Create subtask
subtask_key = create_subtask(
    parent_key=story_key,
    summary="[BE-001.1] Subtask Title",
    description="Subtask description",
    story_points=2,
    estimate_date="2026-07-01"
)

# Change status
change_issue_status(story_key, "In Progress")

# Post comment
post_comment(story_key, "Work completed successfully")
```

## 📋 Fitur

### Jira Client Functions

- `create_story()` - Create Story issue dengan epic link dan sprint
- `create_subtask()` - Create Subtask di bawah parent story
- `get_active_sprint()` - Get active sprint ID dari board
- `change_issue_status()` - Transition issue ke status lain
- `update_story_points()` - Update story point estimation
- `post_comment()` - Post comment ke issue
- `get_issue_details()` - Fetch issue details
- `get_transitions()` - Get available transitions untuk issue

### Auto-Assignment

Semua tickets otomatis di-assign ke user yang configured di `JIRA_EMAIL`.

### Story Points Calculation

1 Story Point = 2 jam kerja (sesuai konvensi di JIRA_TICKETS_FASE_1.md)

### Epic Linking

Semua tickets otomatis di-link ke Epic **SM-BO**.

## 🎯 Workflow untuk Development Task

Setiap kali Kiro (AI Agent) mengerjakan task baru atau bug fix:

1. **Create Ticket** - Buat tiket di Jira (Story/Bug + Subtasks)
2. **Set to In Progress** - Update status ke "In Progress"
3. **Do the Work** - Kerjakan task/fix bug
4. **Set to Done** - Update status ke "Done"
5. **Update Story Points** - Isi story points (1 SP per 2 jam)
6. **Post Comment** - Tambahkan keterangan hasil pekerjaan

## ⚙️ Configuration

Edit `jira_config.py` untuk customize:

- Custom field IDs (story points, epic link, sprint)
- Priority mapping
- Status names
- Issue type names

## 📝 Notes

- Script menggunakan Jira REST API v3
- Authentication menggunakan Basic Auth (email + API token)
- Rate limiting handled dengan `time.sleep()` delays
- Error handling dengan logging comprehensive

## 🔍 Troubleshooting

### Issue: "Failed to create story"

- Check API token masih valid
- Verify project key exists
- Check user punya permission untuk create issues

### Issue: "No active sprint found"

- Buat sprint baru di Jira board
- Set sprint status ke "Active"
- Atau tickets akan dibuat tanpa sprint assignment

### Issue: "Transition not found"

- Workflow di Jira project mungkin berbeda
- Check available transitions dengan `get_transitions()`
- Adjust target status name sesuai workflow

## 📚 References

- [Jira REST API Documentation](https://developer.atlassian.com/cloud/jira/platform/rest/v3/)
- [Jira Agile REST API](https://developer.atlassian.com/cloud/jira/software/rest/)
