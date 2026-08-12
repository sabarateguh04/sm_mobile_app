"""
Jira Client Module
Berisi fungsi-fungsi untuk berinteraksi dengan Jira API
"""
import base64
import requests
import logging
from typing import Optional, Dict, List, Any
from datetime import datetime
from jira_integration.jira_config import (
    JIRA_BASE_URL, JIRA_EMAIL, JIRA_API_TOKEN, JIRA_PROJECT_KEY,
    PRIORITY_MAPPING, STATUS_IN_PROGRESS, STATUS_DONE,
    CUSTOM_FIELD_STORY_POINTS, CUSTOM_FIELD_EPIC_LINK,
    CUSTOM_FIELD_SPRINT, DEFAULT_ASSIGNEE_EMAIL
)

# Setup logging
logging.basicConfig(level=logging.INFO)
log = logging.getLogger(__name__)


def jira_headers() -> Dict[str, str]:
    """Generate authentication headers untuk Jira API"""
    token = base64.b64encode(
        f"{JIRA_EMAIL}:{JIRA_API_TOKEN}".encode()
    ).decode()
    return {
        "Authorization": f"Basic {token}",
        "Content-Type": "application/json"
    }


def get_account_id(email: str) -> Optional[str]:
    """Get Jira account ID dari email address"""
    url = f"{JIRA_BASE_URL}/rest/api/3/user/search"
    params = {"query": email}
    try:
        r = requests.get(url, headers=jira_headers(), params=params, timeout=15)
        if r.ok:
            users = r.json()
            if users and len(users) > 0:
                account_id = users[0].get("accountId")
                log.info(f"Found account ID for {email}: {account_id}")
                return account_id
        else:
            log.warning(f"Failed to get account ID for {email}: {r.text}")
    except Exception as e:
        log.error(f"Error getting account ID: {e}")
    return None


def get_active_sprint() -> Optional[int]:
    """Get active sprint ID dari board"""
    from jira_integration.jira_config import JIRA_BOARD_ID
    url = f"{JIRA_BASE_URL}/rest/agile/1.0/board/{JIRA_BOARD_ID}/sprint"
    params = {"state": "active"}
    try:
        r = requests.get(url, headers=jira_headers(), params=params, timeout=15)
        if r.ok:
            sprints = r.json().get("values", [])
            if sprints:
                sprint_id = sprints[0].get("id")
                sprint_name = sprints[0].get("name")
                log.info(f"Found active sprint: {sprint_name} (ID: {sprint_id})")
                return sprint_id
            else:
                log.warning("No active sprint found")
        else:
            log.warning(f"Failed to get active sprint: {r.text}")
    except Exception as e:
        log.error(f"Error getting active sprint: {e}")
    return None


def create_story(
    summary: str,
    description: str,
    priority: str,
    story_points: int,
    start_date: str,
    due_date: str,
    epic_key: Optional[str] = None,
    sprint_id: Optional[int] = None,
    assignee_email: Optional[str] = None
) -> Optional[str]:
    """
    Create Story issue di Jira
    
    Args:
        summary: Story title (akan ditambahkan prefix [BE]/[FE])
        description: Story description
        priority: Priority level (Highest, High, Medium, Low)
        story_points: Story point estimation
        start_date: Start date (format: YYYY-MM-DD)
        due_date: Due date (format: YYYY-MM-DD)
        epic_key: Epic key untuk link (optional)
        sprint_id: Sprint ID (optional)
        assignee_email: Email assignee (optional, default dari config)
        
    Returns:
        Created issue key (e.g., "DDP-123") atau None jika gagal
    """
    url = f"{JIRA_BASE_URL}/rest/api/3/issue"
    
    # Get assignee account ID
    assignee_email = assignee_email or DEFAULT_ASSIGNEE_EMAIL
    account_id = get_account_id(assignee_email)
    
    # Build payload
    payload = {
        "fields": {
            "project": {"key": JIRA_PROJECT_KEY},
            "summary": summary,
            "description": {
                "type": "doc",
                "version": 1,
                "content": [
                    {
                        "type": "paragraph",
                        "content": [{"type": "text", "text": description}]
                    }
                ]
            },
            "issuetype": {"name": "Story"},
            "priority": {"id": PRIORITY_MAPPING.get(priority, "3")},
            "duedate": due_date,
        }
    }
    
    # Add optional fields
    if account_id:
        payload["fields"]["assignee"] = {"accountId": account_id}
    
    # Note: Story Points, Epic Link, Sprint may not be available on Story screen
    # These will be added via update API after creation if needed
    # if story_points:
    #     payload["fields"][CUSTOM_FIELD_STORY_POINTS] = story_points
    # if epic_key:
    #     payload["fields"][CUSTOM_FIELD_EPIC_LINK] = epic_key
    # if sprint_id:
    #     payload["fields"][CUSTOM_FIELD_SPRINT] = sprint_id
    
    # Start date (custom field, might vary)
    # Jira biasanya tidak punya built-in start date, ini optional
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok:
            issue_key = r.json().get("key")
            log.info(f"Created story: {issue_key} - {summary}")
            return issue_key
        else:
            log.error(f"Failed to create story: {r.text}")
            return None
    except Exception as e:
        log.error(f"Error creating story: {e}")
        return None


def get_subtask_issue_type_id() -> Optional[str]:
    """Get Sub-task issue type ID dari project"""
    url = f"{JIRA_BASE_URL}/rest/api/3/project/{JIRA_PROJECT_KEY}"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            data = r.json()
            issue_types = data.get("issueTypes", [])
            for it in issue_types:
                if it.get("subtask") == True:
                    log.info(f"Found Sub-task type ID: {it['id']}")
                    return it["id"]
        log.error("Sub-task issue type not found")
        return None
    except Exception as e:
        log.error(f"Error getting issue types: {e}")
        return None


def create_subtask(
    parent_key: str,
    summary: str,
    description: str,
    story_points: int,
    estimate_date: str,
    assignee_email: Optional[str] = None
) -> Optional[str]:
    """
    Create Subtask issue di Jira (REAL subtask dengan parent field)
    
    Args:
        parent_key: Parent story key (e.g., "DDP-123")
        summary: Subtask title
        description: Subtask description
        story_points: Story point estimation
        estimate_date: Estimate date (format: YYYY-MM-DD)
        assignee_email: Email assignee (optional)
        
    Returns:
        Created subtask key atau None jika gagal
    """
    url = f"{JIRA_BASE_URL}/rest/api/3/issue"
    
    # Get subtask issue type ID
    subtask_type_id = get_subtask_issue_type_id()
    if not subtask_type_id:
        log.error("Cannot create subtask - Sub-task type not available")
        return None
    
    # Get assignee account ID
    assignee_email = assignee_email or DEFAULT_ASSIGNEE_EMAIL
    account_id = get_account_id(assignee_email)
    
    # Build payload dengan parent field dan subtask type ID
    payload = {
        "fields": {
            "project": {"key": JIRA_PROJECT_KEY},
            "parent": {"key": parent_key},
            "summary": summary,
            "description": {
                "type": "doc",
                "version": 1,
                "content": [
                    {
                        "type": "paragraph",
                        "content": [{"type": "text", "text": description}]
                    }
                ]
            },
            "issuetype": {"id": subtask_type_id},
            "duedate": estimate_date,
        }
    }
    
    # Add optional fields
    if account_id:
        payload["fields"]["assignee"] = {"accountId": account_id}
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok:
            subtask_key = r.json().get("key")
            log.info(f"Created subtask: {subtask_key} under {parent_key}")
            return subtask_key
        else:
            log.error(f"Failed to create subtask: {r.text}")
            return None
    except Exception as e:
        log.error(f"Error creating subtask: {e}")
        return None


def get_transitions(issue_key: str) -> List[Dict[str, Any]]:
    """Get available transitions untuk issue"""
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}/transitions"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            transitions = r.json().get("transitions", [])
            log.info(f"Available transitions for {issue_key}: "
                    f"{[t['name'] for t in transitions]}")
            return transitions
        else:
            log.warning(f"Failed to get transitions for {issue_key}: {r.text}")
    except Exception as e:
        log.error(f"Error getting transitions: {e}")
    return []


def find_transition_id(issue_key: str, target_status: str) -> Optional[str]:
    """Find transition ID berdasarkan target status name (fuzzy match)"""
    transitions = get_transitions(issue_key)
    target_lower = target_status.lower()
    
    for t in transitions:
        name = t.get("name", "").lower()
        to_name = t.get("to", {}).get("name", "").lower()
        
        if target_lower in name or target_lower in to_name:
            log.info(f"Found transition '{t['name']}' (id={t['id']}) for '{target_status}'")
            return t["id"]
    
    log.warning(f"No transition found for '{target_status}' on {issue_key}")
    return None


def change_issue_status(issue_key: str, target_status: str) -> bool:
    """
    Change issue status ke target status
    
    Args:
        issue_key: Issue key (e.g., "DDP-123")
        target_status: Target status name (e.g., "In Progress", "Done")
        
    Returns:
        True jika berhasil, False jika gagal
    """
    transition_id = find_transition_id(issue_key, target_status)
    if not transition_id:
        log.warning(f"Cannot change {issue_key} to '{target_status}' - transition not found")
        return False
    
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}/transitions"
    payload = {"transition": {"id": transition_id}}
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok or r.status_code == 204:
            log.info(f"Changed {issue_key} status to '{target_status}'")
            return True
        else:
            log.warning(f"Failed to change status for {issue_key}: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error changing status: {e}")
        return False


def update_story_points(issue_key: str, story_points: int) -> bool:
    """Update story points untuk issue"""
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}"
    payload = {
        "fields": {
            CUSTOM_FIELD_STORY_POINTS: story_points
        }
    }
    
    try:
        r = requests.put(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok or r.status_code == 204:
            log.info(f"Updated story points for {issue_key}: {story_points} SP")
            return True
        else:
            log.warning(f"Failed to update story points for {issue_key}: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error updating story points: {e}")
        return False


def post_comment(issue_key: str, comment_text: str) -> bool:
    """Post comment ke Jira issue"""
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}/comment"
    
    payload = {
        "body": {
            "type": "doc",
            "version": 1,
            "content": [
                {
                    "type": "paragraph",
                    "content": [{"type": "text", "text": comment_text}]
                }
            ]
        }
    }
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok:
            log.info(f"Posted comment to {issue_key}")
            return True
        else:
            log.warning(f"Failed to post comment to {issue_key}: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error posting comment: {e}")
        return False


def get_issue_details(issue_key: str) -> Optional[Dict[str, Any]]:
    """Get issue details dari Jira"""
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            log.info(f"Fetched issue details for {issue_key}")
            return r.json()
        else:
            log.warning(f"Failed to fetch {issue_key}: {r.text}")
            return None
    except Exception as e:
        log.error(f"Error fetching issue: {e}")
        return None
