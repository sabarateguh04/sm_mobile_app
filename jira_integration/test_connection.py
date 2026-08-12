"""
Test Jira connection dan get available projects
"""
import requests
from jira_integration.jira_client import jira_headers, log
from jira_integration.jira_config import JIRA_BASE_URL


def test_authentication():
    """Test basic authentication"""
    url = f"{JIRA_BASE_URL}/rest/api/3/myself"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            user = r.json()
            log.info("="*60)
            log.info("AUTHENTICATION SUCCESS")
            log.info("="*60)
            log.info(f"Account ID: {user.get('accountId')}")
            log.info(f"Display Name: {user.get('displayName')}")
            log.info(f"Email: {user.get('emailAddress')}")
            log.info(f"Active: {user.get('active')}")
            return True
        else:
            log.error(f"Authentication failed: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error testing authentication: {e}")
        return False


def get_accessible_projects():
    """Get list of accessible projects"""
    url = f"{JIRA_BASE_URL}/rest/api/3/project/search"
    params = {"maxResults": 50}
    try:
        r = requests.get(url, headers=jira_headers(), params=params, timeout=15)
        if r.ok:
            data = r.json()
            projects = data.get("values", [])
            log.info("\n" + "="*60)
            log.info("ACCESSIBLE PROJECTS")
            log.info("="*60)
            if projects:
                for proj in projects:
                    log.info(f"Key: {proj.get('key')} | Name: {proj.get('name')} | Type: {proj.get('projectTypeKey')}")
            else:
                log.warning("No accessible projects found")
            return projects
        else:
            log.error(f"Failed to get projects: {r.text}")
            return []
    except Exception as e:
        log.error(f"Error getting projects: {e}")
        return []


def get_boards():
    """Get list of boards"""
    url = f"{JIRA_BASE_URL}/rest/agile/1.0/board"
    params = {"maxResults": 50}
    try:
        r = requests.get(url, headers=jira_headers(), params=params, timeout=15)
        if r.ok:
            data = r.json()
            boards = data.get("values", [])
            log.info("\n" + "="*60)
            log.info("ACCESSIBLE BOARDS")
            log.info("="*60)
            if boards:
                for board in boards:
                    log.info(f"ID: {board.get('id')} | Name: {board.get('name')} | Type: {board.get('type')}")
            else:
                log.warning("No accessible boards found")
            return boards
        else:
            log.error(f"Failed to get boards: {r.text}")
            return []
    except Exception as e:
        log.error(f"Error getting boards: {e}")
        return []


def get_issue_types(project_key: str):
    """Get available issue types for project"""
    url = f"{JIRA_BASE_URL}/rest/api/3/project/{project_key}"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            project = r.json()
            issue_types = project.get("issueTypes", [])
            log.info("\n" + "="*60)
            log.info(f"ISSUE TYPES for {project_key}")
            log.info("="*60)
            for it in issue_types:
                log.info(f"Name: {it.get('name')} | ID: {it.get('id')} | Subtask: {it.get('subtask')}")
            return issue_types
        else:
            log.error(f"Failed to get issue types: {r.text}")
            return []
    except Exception as e:
        log.error(f"Error getting issue types: {e}")
        return []


def get_custom_fields():
    """Get list of custom fields"""
    url = f"{JIRA_BASE_URL}/rest/api/3/field"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            fields = r.json()
            custom_fields = [f for f in fields if f.get("custom", False)]
            log.info("\n" + "="*60)
            log.info("CUSTOM FIELDS (relevant ones)")
            log.info("="*60)
            for field in custom_fields:
                name = field.get("name", "")
                if any(keyword in name.lower() for keyword in ["story", "point", "epic", "sprint"]):
                    log.info(f"ID: {field.get('id')} | Name: {name}")
            return custom_fields
        else:
            log.error(f"Failed to get custom fields: {r.text}")
            return []
    except Exception as e:
        log.error(f"Error getting custom fields: {e}")
        return []


if __name__ == "__main__":
    log.info("="*60)
    log.info("JIRA CONNECTION TEST")
    log.info("="*60)
    
    # Test authentication
    if not test_authentication():
        log.error("Authentication failed. Check your credentials in .env file.")
        exit(1)
    
    # Get projects
    projects = get_accessible_projects()
    
    # Get boards
    boards = get_boards()
    
    # Get custom fields
    get_custom_fields()
    
    # If we have projects, get issue types for first one
    if projects:
        first_project_key = projects[0].get("key")
        get_issue_types(first_project_key)
    
    log.info("\n" + "="*60)
    log.info("TEST COMPLETED")
    log.info("="*60)
    log.info("Use the information above to update jira_config.py with correct:")
    log.info("- JIRA_PROJECT_KEY")
    log.info("- JIRA_BOARD_ID")
    log.info("- CUSTOM_FIELD_* IDs")
