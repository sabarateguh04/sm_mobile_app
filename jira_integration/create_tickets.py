"""
Script untuk create Jira tickets berdasarkan JIRA_TICKETS_FASE_1.md
Membuat Story + Subtasks untuk FASE 1: AI Foundation
"""
import time
from typing import List, Dict, Tuple
from jira_integration.jira_client import (
    create_story, create_subtask, change_issue_status,
    get_active_sprint, update_story_points, log
)
from jira_integration.jira_config import EPIC_KEY_SM_BO


# ==================== DATA STRUKTUR TICKETS ====================

def get_backend_stories() -> List[Dict]:
    """Backend stories untuk FASE 1"""
    return [
        {
            "key": "BE-F1-002",
            "summary": "[BE] Implement RAG Search System",
            "description": "Membangun Retrieval-Augmented Generation (RAG) system untuk search dokumen perusahaan, SOP, dan knowledge articles dengan semantic search.",
            "priority": "High",
            "story_points": 6,
            "start_date": "2026-07-03",
            "due_date": "2026-07-05",
            "acceptance_criteria": """
- Semantic search return relevant documents dengan similarity > 0.85
- Search latency < 500ms untuk 1000+ documents
- Support multi-language search (Indonesian & English)
            """,
            "subtasks": [
                {
                    "summary": "[BE-F1-002.1] Setup vector database (Pinecone/Weaviate)",
                    "description": "Configure vector DB, setup indexes, implement CRUD operations",
                    "story_points": 2,
                    "estimate_date": "2026-07-03"
                },
                {
                    "summary": "[BE-F1-002.2] Implement document chunking & embedding",
                    "description": "Split documents into chunks, generate embeddings, store vectors",
                    "story_points": 2,
                    "estimate_date": "2026-07-04"
                },
                {
                    "summary": "[BE-F1-002.3] Build semantic search engine",
                    "description": "Implement similarity search, ranking algorithm, result filtering",
                    "story_points": 1,
                    "estimate_date": "2026-07-04"
                },
                {
                    "summary": "[BE-F1-002.4] Testing & optimization search accuracy",
                    "description": "Evaluate search results, optimize threshold, benchmark performance",
                    "story_points": 1,
                    "estimate_date": "2026-07-05"
                }
            ]
        },
        {
            "key": "BE-F1-003",
            "summary": "[BE] Build Prompt Management System",
            "description": "Membangun dynamic prompt management system dengan template versioning, A/B testing capability, dan context injection untuk persona Alexa.",
            "priority": "High",
            "story_points": 4,
            "start_date": "2026-07-05",
            "due_date": "2026-07-06",
            "acceptance_criteria": """
- Prompt templates dapat di-update tanpa code deployment
- Context injection bekerja untuk user profile + conversation history
- A/B testing dapat track performance per prompt variant
            """,
            "subtasks": [
                {
                    "summary": "[BE-F1-003.1] Create prompt template engine",
                    "description": "Build template parser, variable injection, version control",
                    "story_points": 2,
                    "estimate_date": "2026-07-05"
                },
                {
                    "summary": "[BE-F1-003.2] Implement context injection system",
                    "description": "User context, conversation history, knowledge context injection",
                    "story_points": 1,
                    "estimate_date": "2026-07-06"
                },
                {
                    "summary": "[BE-F1-003.3] Build A/B testing framework",
                    "description": "Multiple prompt variants, performance tracking, auto-selection",
                    "story_points": 1,
                    "estimate_date": "2026-07-06"
                }
            ]
        },
        {
            "key": "BE-F1-004",
            "summary": "[BE] Implement Content Memory System",
            "description": "Membangun conversation memory system dengan short-term memory (session-based) dan long-term memory (persistent storage) untuk personalisasi.",
            "priority": "Medium",
            "story_points": 5,
            "start_date": "2026-07-08",
            "due_date": "2026-07-10",
            "acceptance_criteria": """
- Short-term memory (session) persist selama 24 jam
- Long-term memory retrieve relevant conversations dengan similarity > 0.8
- Memory context injection tidak exceed LLM context window limit
            """,
            "subtasks": [
                {
                    "summary": "[BE-F1-004.1] Setup Redis session memory",
                    "description": "Configure Redis, implement session store, TTL management",
                    "story_points": 1,
                    "estimate_date": "2026-07-08"
                },
                {
                    "summary": "[BE-F1-004.2] Build conversation history storage",
                    "description": "PostgreSQL schema, CRUD operations, pagination",
                    "story_points": 2,
                    "estimate_date": "2026-07-09"
                },
                {
                    "summary": "[BE-F1-004.3] Implement memory retrieval & context injection",
                    "description": "Semantic memory search, relevance ranking, LLM context injection",
                    "story_points": 2,
                    "estimate_date": "2026-07-10"
                }
            ]
        },
        {
            "key": "BE-F1-005",
            "summary": "[BE] Develop Multi-turn Conversation Engine",
            "description": "Membangun conversation engine yang support multi-turn dialogue, context preservation, dan clarification questions.",
            "priority": "High",
            "story_points": 6,
            "start_date": "2026-07-10",
            "due_date": "2026-07-12",
            "acceptance_criteria": """
- Support minimal 10 conversation turns tanpa context loss
- Clarification questions muncul saat ambiguity > threshold
- Context window management otomatis truncate dengan priority system
            """,
            "subtasks": [
                {
                    "summary": "[BE-F1-005.1] Implement conversation state machine",
                    "description": "State definitions, transition logic, guard conditions",
                    "story_points": 2,
                    "estimate_date": "2026-07-10"
                },
                {
                    "summary": "[BE-F1-005.2] Build context window management",
                    "description": "Truncation strategy, priority ranking, compression algorithm",
                    "story_points": 2,
                    "estimate_date": "2026-07-11"
                },
                {
                    "summary": "[BE-F1-005.3] Develop clarification & follow-up logic",
                    "description": "Ambiguity detection, question generation, context tracking",
                    "story_points": 2,
                    "estimate_date": "2026-07-12"
                }
            ]
        },
        {
            "key": "BE-F1-006",
            "summary": "[BE] Build Speech-to-Text Engine",
            "description": "Implementasi STT engine menggunakan Whisper/Google Speech-to-Text dengan support Bahasa Indonesia, noise reduction, dan real-time transcription.",
            "priority": "High",
            "story_points": 5,
            "start_date": "2026-07-15",
            "due_date": "2026-07-17",
            "acceptance_criteria": """
- STT accuracy > 90% untuk Bahasa Indonesia
- Transcription latency < 2 detik untuk audio 5 detik
- Support real-time streaming dengan progressive results
            """,
            "subtasks": [
                {
                    "summary": "[BE-F1-006.1] Setup Whisper API integration",
                    "description": "API client setup, audio format handling, language detection",
                    "story_points": 2,
                    "estimate_date": "2026-07-15"
                },
                {
                    "summary": "[BE-F1-006.2] Implement audio preprocessing",
                    "description": "Noise reduction, normalization, format conversion (WAV/WebM)",
                    "story_points": 2,
                    "estimate_date": "2026-07-16"
                },
                {
                    "summary": "[BE-F1-006.3] Build real-time streaming STT",
                    "description": "WebSocket integration, chunk processing, progressive transcription",
                    "story_points": 1,
                    "estimate_date": "2026-07-17"
                }
            ]
        }
    ]


def get_frontend_stories() -> List[Dict]:
    """Frontend stories untuk FASE 1"""
    return [
        {
            "key": "FE-F1-002",
            "summary": "[FE] Implement Voice Recorder with VAD",
            "description": "Membangun voice recording system dengan Voice Activity Detection (VAD), noise reduction, dan automatic stop detection.",
            "priority": "High",
            "story_points": 6,
            "start_date": "2026-07-25",
            "due_date": "2026-07-27",
            "acceptance_criteria": """
- VAD detect speech start < 200ms
- Auto-stop after 1.5s silence
- Recording quality 16kHz mono WAV
- Visual feedback real-time (waveform/bars)
            """,
            "subtasks": [
                {
                    "summary": "[FE-F1-002.1] Setup MediaRecorder API",
                    "description": "Microphone permission, audio stream capture, WAV encoding",
                    "story_points": 2,
                    "estimate_date": "2026-07-25"
                },
                {
                    "summary": "[FE-F1-002.2] Integrate VAD (Voice Activity Detection)",
                    "description": "@ricky0123/vad-web integration, silence detection, auto-stop logic",
                    "story_points": 2,
                    "estimate_date": "2026-07-26"
                },
                {
                    "summary": "[FE-F1-002.3] Build recording UI & controls",
                    "description": "Record button, voice visualizer, status indicators",
                    "story_points": 2,
                    "estimate_date": "2026-07-27"
                }
            ]
        },
        {
            "key": "FE-F1-006",
            "summary": "[FE] Add Biometric Face Recognition",
            "description": "Implementasi face recognition menggunakan InsightFace untuk user authentication dan personalization.",
            "priority": "Medium",
            "story_points": 6,
            "start_date": "2026-08-05",
            "due_date": "2026-08-07",
            "acceptance_criteria": """
- Face detection real-time < 100ms per frame
- Recognition accuracy > 95% untuk registered users
- Registration flow capture 3-5 angles untuk robust matching
- Graceful fallback jika webcam tidak tersedia
            """,
            "subtasks": [
                {
                    "summary": "[FE-F1-006.1] Setup webcam capture & canvas rendering",
                    "description": "getUserMedia API, video stream, canvas draw loop",
                    "story_points": 2,
                    "estimate_date": "2026-08-05"
                },
                {
                    "summary": "[FE-F1-006.2] Integrate InsightFace detection",
                    "description": "Face detection API calls, embedding generation, matching",
                    "story_points": 2,
                    "estimate_date": "2026-08-06"
                },
                {
                    "summary": "[FE-F1-006.3] Build registration & recognition UI",
                    "description": "Register flow, recognize flow, user feedback, error handling",
                    "story_points": 2,
                    "estimate_date": "2026-08-07"
                }
            ]
        }
    ]


def get_integration_stories() -> List[Dict]:
    """Integration & Testing stories untuk FASE 1"""
    return [
        {
            "key": "INT-F1-001",
            "summary": "[BE] End-to-End Integration Testing",
            "description": "Testing end-to-end flow dari voice input → STT → LLM → TTS → audio playback dengan berbagai scenarios dan edge cases.",
            "priority": "High",
            "story_points": 6,
            "start_date": "2026-08-10",
            "due_date": "2026-08-12",
            "acceptance_criteria": """
- 100+ test cases covering happy path + edge cases
- Code coverage > 80% untuk core modules
- Load test pass untuk 50 concurrent users
- E2E latency < 5 detik untuk typical conversation
            """,
            "subtasks": [
                {
                    "summary": "[INT-F1-001.1] Setup testing framework & fixtures",
                    "description": "Pytest setup, mock data, test fixtures, database seeding",
                    "story_points": 2,
                    "estimate_date": "2026-08-10"
                },
                {
                    "summary": "[INT-F1-001.2] Write integration test suites",
                    "description": "Happy path, error scenarios, timeout handling, cache validation",
                    "story_points": 3,
                    "estimate_date": "2026-08-11"
                },
                {
                    "summary": "[INT-F1-001.3] Performance & load testing",
                    "description": "Concurrent users, stress testing, latency benchmarking",
                    "story_points": 1,
                    "estimate_date": "2026-08-12"
                }
            ]
        },
        {
            "key": "INT-F1-002",
            "summary": "[BE] API Documentation & Deployment",
            "description": "Create API documentation menggunakan OpenAPI/Swagger, setup CI/CD pipeline, dan prepare production deployment.",
            "priority": "Medium",
            "story_points": 4,
            "start_date": "2026-08-12",
            "due_date": "2026-08-13",
            "acceptance_criteria": """
- API documentation accessible via /docs endpoint
- CI/CD pipeline auto-deploy on main branch push
- Production environment pass health checks
- Docker compose setup untuk easy deployment
            """,
            "subtasks": [
                {
                    "summary": "[INT-F1-002.1] Generate OpenAPI documentation",
                    "description": "Swagger setup, endpoint documentation, request/response schemas",
                    "story_points": 2,
                    "estimate_date": "2026-08-12"
                },
                {
                    "summary": "[INT-F1-002.2] Setup CI/CD pipeline",
                    "description": "GitHub Actions, automated testing, Docker build",
                    "story_points": 1,
                    "estimate_date": "2026-08-13"
                },
                {
                    "summary": "[INT-F1-002.3] Prepare production environment",
                    "description": "VPS setup, environment variables, health checks",
                    "story_points": 1,
                    "estimate_date": "2026-08-13"
                }
            ]
        },
        {
            "key": "INT-F1-003",
            "summary": "[BE] Monitoring & Logging Setup",
            "description": "Implementasi comprehensive logging, metrics collection, dan error monitoring untuk production observability.",
            "priority": "Medium",
            "story_points": 3,
            "start_date": "2026-08-14",
            "due_date": "2026-08-15",
            "acceptance_criteria": """
- Structured logs dengan trace ID untuk request tracking
- Metrics dashboard untuk monitoring key metrics
- Error tracking dengan automatic alerting
- Log retention 30 hari dengan rotation
            """,
            "subtasks": [
                {
                    "summary": "[INT-F1-003.1] Setup structured logging",
                    "description": "Loguru integration, log levels, JSON formatting",
                    "story_points": 1,
                    "estimate_date": "2026-08-14"
                },
                {
                    "summary": "[INT-F1-003.2] Implement metrics collection",
                    "description": "Response time, cache hit rate, error rate, queue depth",
                    "story_points": 1,
                    "estimate_date": "2026-08-14"
                },
                {
                    "summary": "[INT-F1-003.3] Add error tracking",
                    "description": "Sentry integration, error grouping, alerting rules",
                    "story_points": 1,
                    "estimate_date": "2026-08-15"
                }
            ]
        }
    ]


# ==================== MAIN EXECUTION ====================

def create_all_tickets():
    """Main function untuk create semua tickets"""
    log.info("="*60)
    log.info("JIRA TICKET CREATION - FASE 1: AI FOUNDATION")
    log.info("="*60)
    
    # Get active sprint
    sprint_id = get_active_sprint()
    if not sprint_id:
        log.warning("No active sprint found. Tickets will be created without sprint assignment.")
    
    # Combine all stories
    all_stories = (
        get_backend_stories() +
        get_frontend_stories() +
        get_integration_stories()
    )
    
    created_tickets = []
    
    for story_data in all_stories:
        log.info(f"\n{'='*60}")
        log.info(f"Creating Story: {story_data['key']} - {story_data['summary']}")
        log.info(f"{'='*60}")
        
        # Create full description dengan acceptance criteria
        full_description = f"{story_data['description']}\n\n"
        full_description += f"Acceptance Criteria:\n{story_data['acceptance_criteria']}"
        
        # Create story
        story_key = create_story(
            summary=story_data['summary'],
            description=full_description,
            priority=story_data['priority'],
            story_points=story_data['story_points'],
            start_date=story_data['start_date'],
            due_date=story_data['due_date'],
            epic_key=EPIC_KEY_SM_BO,
            sprint_id=sprint_id
        )
        
        if not story_key:
            log.error(f"Failed to create story {story_data['key']}, skipping subtasks...")
            continue
        
        created_tickets.append({
            "key": story_key,
            "type": "Story",
            "summary": story_data['summary']
        })
        
        # Small delay to avoid rate limiting
        time.sleep(1)
        
        # Create subtasks
        subtask_keys = []
        for idx, subtask_data in enumerate(story_data['subtasks'], 1):
            log.info(f"  Creating subtask {idx}/{len(story_data['subtasks'])}: {subtask_data['summary']}")
            
            subtask_key = create_subtask(
                parent_key=story_key,
                summary=subtask_data['summary'],
                description=subtask_data['description'],
                story_points=subtask_data['story_points'],
                estimate_date=subtask_data['estimate_date']
            )
            
            if subtask_key:
                subtask_keys.append(subtask_key)
                created_tickets.append({
                    "key": subtask_key,
                    "type": "Subtask",
                    "summary": subtask_data['summary'],
                    "parent": story_key
                })
            
            # Small delay
            time.sleep(0.5)
        
        log.info(f"Created story {story_key} with {len(subtask_keys)} subtasks")
        
        # Delay before next story
        time.sleep(2)
    
    # Summary
    log.info(f"\n{'='*60}")
    log.info("TICKET CREATION SUMMARY")
    log.info(f"{'='*60}")
    log.info(f"Total tickets created: {len(created_tickets)}")
    
    stories = [t for t in created_tickets if t['type'] == 'Story']
    subtasks = [t for t in created_tickets if t['type'] == 'Subtask']
    
    log.info(f"  - Stories: {len(stories)}")
    log.info(f"  - Subtasks: {len(subtasks)}")
    
    log.info(f"\nCreated Stories:")
    for story in stories:
        log.info(f"  {story['key']}: {story['summary']}")
    
    log.info(f"\n{'='*60}")
    log.info("DONE! Check your Jira board for the created tickets.")
    log.info(f"{'='*60}")
    
    return created_tickets


if __name__ == "__main__":
    try:
        created_tickets = create_all_tickets()
    except KeyboardInterrupt:
        log.warning("\nScript interrupted by user")
    except Exception as e:
        log.error(f"Fatal error: {e}", exc_info=True)
