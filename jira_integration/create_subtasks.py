"""
Create Subtasks as Task Type
Karena Sub-task type tidak tersedia, kita create sebagai Task dan link manual
"""
import logging
from jira_integration.jira_client import create_subtask, get_account_id
from jira_integration.jira_config import DEFAULT_ASSIGNEE_EMAIL

logging.basicConfig(level=logging.INFO)
log = logging.getLogger(__name__)

# Mapping parent story ke subtasks
SUBTASKS_DATA = {
    "DDP-816": [
        {
            "summary": "[BE-F1-002.1] Setup vector database (Pinecone/Weaviate)",
            "description": "Setup dan konfigurasi vector database untuk menyimpan embeddings dokumen. Pilih antara Pinecone atau Weaviate.",
            "story_points": 2,
            "due_date": "2026-07-02"
        },
        {
            "summary": "[BE-F1-002.2] Implement document chunking & embedding",
            "description": "Implementasi logic untuk split dokumen menjadi chunks dan generate embeddings menggunakan model yang sesuai.",
            "story_points": 2,
            "due_date": "2026-07-03"
        },
        {
            "summary": "[BE-F1-002.3] Build semantic search engine",
            "description": "Build search engine dengan similarity search untuk RAG retrieval.",
            "story_points": 1,
            "due_date": "2026-07-04"
        },
        {
            "summary": "[BE-F1-002.4] Testing & optimization search accuracy",
            "description": "Testing accuracy dan optimization untuk search results relevance.",
            "story_points": 1,
            "due_date": "2026-07-05"
        }
    ],
    "DDP-817": [
        {
            "summary": "[BE-F1-003.1] Create prompt template engine",
            "description": "Build template engine untuk dynamic prompt generation dengan variable injection.",
            "story_points": 2,
            "due_date": "2026-07-05"
        },
        {
            "summary": "[BE-F1-003.2] Implement context injection system",
            "description": "System untuk inject context dari RAG, memory, dan user data ke dalam prompts.",
            "story_points": 1,
            "due_date": "2026-07-06"
        },
        {
            "summary": "[BE-F1-003.3] Build A/B testing framework",
            "description": "Framework untuk A/B testing different prompt variations dan track performance.",
            "story_points": 1,
            "due_date": "2026-07-06"
        }
    ],
    "DDP-818": [
        {
            "summary": "[BE-F1-004.1] Setup Redis session memory",
            "description": "Setup Redis untuk short-term session memory storage dengan TTL management.",
            "story_points": 2,
            "due_date": "2026-07-08"
        },
        {
            "summary": "[BE-F1-004.2] Build conversation history storage",
            "description": "Implement conversation history storage di PostgreSQL dengan efficient querying.",
            "story_points": 2,
            "due_date": "2026-07-09"
        },
        {
            "summary": "[BE-F1-004.3] Implement memory retrieval & context injection",
            "description": "Logic untuk retrieve relevant memory dan inject ke conversation context.",
            "story_points": 1,
            "due_date": "2026-07-10"
        }
    ],
    "DDP-819": [
        {
            "summary": "[BE-F1-005.1] Implement conversation state machine",
            "description": "State machine untuk manage conversation flow dan detect conversation stages.",
            "story_points": 2,
            "due_date": "2026-07-10"
        },
        {
            "summary": "[BE-F1-005.2] Build context window management",
            "description": "Smart context window management untuk handle token limits dengan conversation trimming.",
            "story_points": 2,
            "due_date": "2026-07-11"
        },
        {
            "summary": "[BE-F1-005.3] Develop clarification & follow-up logic",
            "description": "Logic untuk detect unclear questions dan trigger clarification atau follow-up.",
            "story_points": 2,
            "due_date": "2026-07-12"
        }
    ],
    "DDP-820": [
        {
            "summary": "[BE-F1-006.1] Setup Whisper API integration",
            "description": "Integrate Whisper model untuk STT dengan optimal configuration.",
            "story_points": 2,
            "due_date": "2026-07-15"
        },
        {
            "summary": "[BE-F1-006.2] Implement audio preprocessing",
            "description": "Audio preprocessing pipeline (noise reduction, normalization, format conversion).",
            "story_points": 2,
            "due_date": "2026-07-16"
        },
        {
            "summary": "[BE-F1-006.3] Build real-time streaming STT",
            "description": "Real-time streaming STT dengan WebSocket untuk low latency transcription.",
            "story_points": 1,
            "due_date": "2026-07-17"
        }
    ],
    "DDP-821": [
        {
            "summary": "[FE-F1-002.1] Setup MediaRecorder API",
            "description": "Setup browser MediaRecorder API untuk capture audio dari microphone.",
            "story_points": 2,
            "due_date": "2026-07-25"
        },
        {
            "summary": "[FE-F1-002.2] Integrate VAD (Voice Activity Detection)",
            "description": "Integrate VAD library untuk detect speech vs silence dan auto stop recording.",
            "story_points": 3,
            "due_date": "2026-07-26"
        },
        {
            "summary": "[FE-F1-002.3] Build recording UI & controls",
            "description": "Build UI controls untuk start/stop recording dengan visual feedback (waveform, status).",
            "story_points": 1,
            "due_date": "2026-07-27"
        }
    ],
    "DDP-822": [
        {
            "summary": "[FE-F1-006.1] Setup webcam capture & canvas rendering",
            "description": "Setup webcam stream capture dan render ke canvas untuk face detection.",
            "story_points": 2,
            "due_date": "2026-08-05"
        },
        {
            "summary": "[FE-F1-006.2] Integrate InsightFace detection",
            "description": "Integrate InsightFace model untuk face detection dan recognition.",
            "story_points": 3,
            "due_date": "2026-08-06"
        },
        {
            "summary": "[FE-F1-006.3] Build registration & recognition UI",
            "description": "Build UI untuk user registration dan face recognition flow.",
            "story_points": 1,
            "due_date": "2026-08-07"
        }
    ],
    "DDP-823": [
        {
            "summary": "[INT-F1-001.1] Setup testing framework & fixtures",
            "description": "Setup testing framework (pytest) dengan fixtures untuk integration tests.",
            "story_points": 2,
            "due_date": "2026-08-10"
        },
        {
            "summary": "[INT-F1-001.2] Write integration test suites",
            "description": "Write comprehensive integration tests untuk all API endpoints dan workflows.",
            "story_points": 3,
            "due_date": "2026-08-11"
        },
        {
            "summary": "[INT-F1-001.3] Performance & load testing",
            "description": "Performance testing dengan load simulation dan identify bottlenecks.",
            "story_points": 1,
            "due_date": "2026-08-12"
        }
    ],
    "DDP-824": [
        {
            "summary": "[INT-F1-002.1] Generate OpenAPI documentation",
            "description": "Generate comprehensive OpenAPI/Swagger documentation untuk all endpoints.",
            "story_points": 1,
            "due_date": "2026-08-12"
        },
        {
            "summary": "[INT-F1-002.2] Setup CI/CD pipeline",
            "description": "Setup CI/CD pipeline dengan GitHub Actions untuk automated testing dan deployment.",
            "story_points": 2,
            "due_date": "2026-08-13"
        },
        {
            "summary": "[INT-F1-002.3] Prepare production environment",
            "description": "Prepare production environment configuration, secrets management, dan deployment script.",
            "story_points": 1,
            "due_date": "2026-08-13"
        }
    ],
    "DDP-825": [
        {
            "summary": "[INT-F1-003.1] Setup structured logging",
            "description": "Setup structured logging dengan JSON format untuk better observability.",
            "story_points": 1,
            "due_date": "2026-08-13"
        },
        {
            "summary": "[INT-F1-003.2] Implement metrics collection",
            "description": "Implement metrics collection (response time, error rate, throughput) dengan Prometheus.",
            "story_points": 1,
            "due_date": "2026-08-14"
        },
        {
            "summary": "[INT-F1-003.3] Add error tracking",
            "description": "Add error tracking dan alerting system dengan Sentry atau equivalent.",
            "story_points": 1,
            "due_date": "2026-08-15"
        }
    ]
}


def create_all_subtasks():
    """Create all subtasks untuk setiap parent story"""
    log.info("=" * 60)
    log.info("CREATING SUBTASKS AS TASK TYPE")
    log.info("=" * 60)
    
    total_created = 0
    total_failed = 0
    
    for parent_key, subtasks in SUBTASKS_DATA.items():
        log.info(f"\nProcessing parent story: {parent_key}")
        log.info(f"  Total subtasks to create: {len(subtasks)}")
        
        for idx, subtask in enumerate(subtasks, 1):
            log.info(f"  Creating task {idx}/{len(subtasks)}: {subtask['summary']}")
            
            task_key = create_subtask(
                parent_key=parent_key,
                summary=subtask["summary"],
                description=subtask["description"],
                story_points=subtask["story_points"],
                estimate_date=subtask["due_date"],
                assignee_email=DEFAULT_ASSIGNEE_EMAIL
            )
            
            if task_key:
                total_created += 1
            else:
                total_failed += 1
    
    log.info("\n" + "=" * 60)
    log.info("SUBTASK CREATION SUMMARY")
    log.info("=" * 60)
    log.info(f"Total created: {total_created}")
    log.info(f"Total failed: {total_failed}")
    log.info("=" * 60)


if __name__ == "__main__":
    create_all_subtasks()
