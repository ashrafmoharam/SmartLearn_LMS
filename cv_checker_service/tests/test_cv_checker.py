from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_approved_cv():
    response = client.post(
        "/check-cv",
        json={
            "cv_text": "Bachelor degree with 5 years of experience in Python and teaching"
        }
    )

    data = response.json()

    assert data["status"] == "approved"
    assert data["score"] >= 70
    assert "reasons" in data
    assert data["reasons"] == []



def test_rejected_cv():
    response = client.post(
        "/check-cv",
        json={
            "cv_text": "Hello world"
        }
    )

    data = response.json()

    assert data["status"] == "rejected"
    assert data["score"] < 70
    assert "reasons" in data
