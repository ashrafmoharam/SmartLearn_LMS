import uuid
from fastapi import FastAPI
"""
POST /check-cv
Input:
- cv_text: string

Output:
- request_id: uuid
- status: approved | rejected
- score: 0-100
- reasons: list[string]
"""


app = FastAPI()

@app.post("/check-cv")
def check_cv(data: dict):
    request_id = str(uuid.uuid4())
    cv = data.get("cv_text", "").lower()

    score = 0
    reasons = []

    if not cv.strip():
        return {
            "request_id": request_id,
            "status": "rejected",
            "score": 0,
            "reasons": ["CV text is missing"]
        }

    if "bachelor" in cv or "degree" in cv:
        score += 15
    else:
        reasons.append("No valid academic degree found")

    if "year" in cv:
        score += 35
    else:
        reasons.append("Insufficient experience")

    if any(skill in cv for skill in ["python", "java", "flutter", "ai"]):
        score += 30
    else:
        reasons.append("No technical skills mentioned")

    if "teach" in cv or "instructor" in cv:
        score += 20
    else:
        reasons.append("No teaching experience found")

    status = "approved" if score >= 70 else "rejected"

    return {
        "request_id": request_id,
        "status": status,
        "score": score,
        "reasons": [] if status == "approved" else reasons
    }
