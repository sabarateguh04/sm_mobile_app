"""
Link Task tickets ke Parent Stories sebagai subtasks
Karena Sub-task type tidak tersedia, kita link Task ke parent via Issue Links
"""
import logging
import requests
from jira_integration.jira_client import jira_headers
from jira_integration.jira_config import JIRA_BASE_URL

logging.basicConfig(level=logging.INFO)
log = logging.getLogger(__name__)

# Mapping task ke parent story
TASK_TO_PARENT = {
    # DDP-816 subtasks
    "DDP-826": "DDP-816",
    "DDP-827": "DDP-816",
    "DDP-828": "DDP-816",
    "DDP-829": "DDP-816",
    
    # DDP-817 subtasks
    "DDP-830": "DDP-817",
    "DDP-831": "DDP-817",
    "DDP-832": "DDP-817",
    
    # DDP-818 subtasks
    "DDP-833": "DDP-818",
    "DDP-834": "DDP-818",
    "DDP-835": "DDP-818",
    
    # DDP-819 subtasks
    "DDP-836": "DDP-819",
    "DDP-837": "DDP-819",
    "DDP-838": "DDP-819",
    
    # DDP-820 subtasks
    "DDP-839": "DDP-820",
    "DDP-840": "DDP-820",
    "DDP-841": "DDP-820",
    
    # DDP-821 subtasks
    "DDP-842": "DDP-821",
    "DDP-843": "DDP-821",
    "DDP-844": "DDP-821",
    
    # DDP-822 subtasks
    "DDP-845": "DDP-822",
    "DDP-846": "DDP-822",
    "DDP-847": "DDP-822",
    
    # DDP-823 subtasks
    "DDP-848": "DDP-823",
    "DDP-849": "DDP-823",
    "DDP-850": "DDP-823",
    
    # DDP-824 subtasks
    "DDP-851": "DDP-824",
    "DDP-852": "DDP-824",
    "DDP-853": "DDP-824",
    
    # DDP-825 subtasks
    "DDP-854": "DDP-825",
    "DDP-855": "DDP-825",
    "DDP-856": "DDP-825",
}


def convert_to_subtask(issue_key: str, parent_key: str) -> bool:
    """
    Convert Task menjadi Subtask dengan set parent
    
    Note: Ini akan CONVERT issue type dari Task ke Sub-task
    dan set parent field
    """
    url = f"{JIRA_BASE_URL}/rest/api/3/issue/{issue_key}"
    
    # Payload untuk convert ke subtask dan set parent
    payload = {
        "fields": {
            "parent": {"key": parent_key}
        },
        "update": {
            "issueType": [
                {
                    "set": {"name": "Sub-task"}
                }
            ]
        }
    }
    
    try:
        r = requests.put(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok or r.status_code == 204:
            log.info(f"Converted {issue_key} to subtask of {parent_key}")
            return True
        else:
            log.error(f"Failed to convert {issue_key}: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error converting {issue_key}: {e}")
        return False


def link_task_to_parent(task_key: str, parent_key: str) -> bool:
    """
    Link Task ke Parent Story menggunakan 'relates to' link type
    Fallback jika convert ke subtask gagal
    """
    url = f"{JIRA_BASE_URL}/rest/api/3/issueLink"
    
    payload = {
        "type": {"name": "Relates"},
        "inwardIssue": {"key": task_key},
        "outwardIssue": {"key": parent_key},
        "comment": {
            "body": {
                "type": "doc",
                "version": 1,
                "content": [
                    {
                        "type": "paragraph",
                        "content": [{
                            "type": "text",
                            "text": f"This task is part of parent story {parent_key}"
                        }]
                    }
                ]
            }
        }
    }
    
    try:
        r = requests.post(url, headers=jira_headers(), json=payload, timeout=15)
        if r.ok or r.status_code == 201:
            log.info(f"Linked {task_key} to {parent_key}")
            return True
        else:
            log.error(f"Failed to link {task_key}: {r.text}")
            return False
    except Exception as e:
        log.error(f"Error linking {task_key}: {e}")
        return False


def process_all_tasks():
    """Process all tasks untuk link ke parent stories"""
    log.info("=" * 60)
    log.info("CONVERTING TASKS TO SUBTASKS")
    log.info("=" * 60)
    
    total_converted = 0
    total_linked = 0
    total_failed = 0
    
    for task_key, parent_key in TASK_TO_PARENT.items():
        log.info(f"\nProcessing {task_key} -> {parent_key}")
        
        # Try convert to subtask first
        if convert_to_subtask(task_key, parent_key):
            total_converted += 1
        else:
            # Fallback: create link
            log.info(f"  Conversion failed, trying link instead...")
            if link_task_to_parent(task_key, parent_key):
                total_linked += 1
            else:
                total_failed += 1
    
    log.info("\n" + "=" * 60)
    log.info("CONVERSION SUMMARY")
    log.info("=" * 60)
    log.info(f"Converted to subtask: {total_converted}")
    log.info(f"Linked (fallback): {total_linked}")
    log.info(f"Failed: {total_failed}")
    log.info("=" * 60)


if __name__ == "__main__":
    process_all_tasks()
