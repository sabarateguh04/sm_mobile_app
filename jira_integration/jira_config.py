"""
Jira Configuration Module
Berisi konfigurasi dan konstanta untuk integrasi Jira
"""
import os
from dotenv import load_dotenv

load_dotenv()

# Jira Credentials
JIRA_BASE_URL = os.getenv("JIRA_BASE_URL", "https://estrada.atlassian.net")
JIRA_EMAIL = os.getenv("JIRA_EMAIL", "")
JIRA_API_TOKEN = os.getenv("JIRA_API_TOKEN", "")
JIRA_PROJECT_KEY = os.getenv("JIRA_PROJECT_KEY", "DDP")
JIRA_BOARD_ID = os.getenv("JIRA_BOARD_ID", "8")  # DDP board ID

# Epic Key untuk SM-BO (pastikan epic ini sudah dibuat di Jira)
EPIC_KEY_SM_BO = "SM-BO"

# Issue Types
ISSUE_TYPE_STORY = "Story"
ISSUE_TYPE_TASK = "Task"
ISSUE_TYPE_SUBTASK = "Sub-task"
ISSUE_TYPE_BUG = "Bug"

# Priority Mapping (Jira Priority IDs)
PRIORITY_MAPPING = {
    "Highest": "1",
    "High": "2",
    "Medium": "3",
    "Low": "4",
    "Lowest": "5"
}

# Status IDs
STATUS_TODO = "To Do"
STATUS_IN_PROGRESS = "In Progress"
STATUS_DONE = "Done"
STATUS_TESTING = "Testing"

# Custom Fields (dari test_connection hasil)
CUSTOM_FIELD_STORY_POINTS = "customfield_10036"  # Story Points
CUSTOM_FIELD_EPIC_LINK = "customfield_10014"  # Epic Link
CUSTOM_FIELD_SPRINT = "customfield_10020"  # Sprint

# Assignee (email user yang akan di-assign)
DEFAULT_ASSIGNEE_EMAIL = JIRA_EMAIL
