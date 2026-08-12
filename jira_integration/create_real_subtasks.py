"""
Create REAL Subtasks menggunakan issue type ID
Endpoint khusus untuk create subtask dengan parent field
"""
import logging
import requests
from jira_integration.jira_client import jira_headers, get_account_id
from jira_integration.jira_config import JIRA_BASE_URL, JIRA_PROJECT_KEY, DEFAULT_ASSIGNEE_EMAIL

logging.basicConfig(level=logging.INFO)
log = logging.getLogger(__name__)

# Get subtask issue type ID
def get_subtask_issue_type_id() -> str:
    """Get Sub-task issue type ID from project"""
    url = f"{JIRA_BASE_URL}/rest/api/3/project/{JIRA_PROJECT_KEY}"
    try:
        r = requests.get(url, headers=jira_headers(), timeout=15)
        if r.ok:
            data = r.json()
            issue_types = data.get("issueTypes", [])
            for it in issue_types:
                if it.get("subtask") == True:
                    log.info(f"Found Sub-task type ID: {it['id']} (name: {it['name']})")
                    return it["id"]
        log.error("Sub-task issue type not found in project")
        return None
    except Exception as e:
        log.error(f"Error getting issue types: {e}")
        return None


def create_subtask_properly(parent_key: str, summary: str, description: str, due_date: str) -> str:
    """Create subtask dengan issue type ID yang benar"""
    
    # Get subtask type ID
    subtask_type_id = get_subtask_issue_type_id()
    if not subtask_type_id:
        log.error("Cannot create subtask - Sub-task type not available")
        return None
    
    # Get assignee
    account_id = get_account_id(DEFAULT_ASSIGNEE_EMAIL)
    
    url = f"{JIRA_BASE_URL}/rest/api/3/issue"
    
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
            "duedate": due_date,
        }
    }
    
    if account_id:
        payload["fields"]["assignee"] = {"accountId": account_id}
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok:
            key = r.json().get("key")
            log.info(f"Created subtask: {key} under {parent_key}")
            return key
        else:
            log.error(f"Failed to create subtask: {r.text}")
            return None
    except Exception as e:
        log.error(f"Error creating subtask: {e}")
        return None


# Test create 1 subtask
if __name__ == "__main__":
    log.info("Testing subtask creation...")
    
    # Test dengan DDP-816
    result = create_subtask_properly(
        parent_key="DDP-816",
        summary="[TEST] Sample Subtask",
        description="This is a test subtask to verify API works",
        due_date="2026-07-30"
    )
    
    if result:
        log.info(f"SUCCESS! Created subtask: {result}")
    else:
        log.error("FAILED! Cannot create subtask via API")
