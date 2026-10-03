import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api, { getCurrentUser } from '../../api';

export default function SupervisorDashboard() {
  const user = getCurrentUser();
  const [data, setData]     = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/supervisor/students').then(r => { setData(r.data); setLoading(false); })
      .catch(() => setLoading(false));
  }, []);

  if (loading) return <div className="loading-spinner"><div className="spinner" /></div>;

  const students       = data?.students ?? [];
  const pending        = data?.pending_reviews ?? [];

  const handleReview = (milestoneId, decision) => {
    if (decision === 'rejected' && !window.confirm('Are you sure you want to reject this submission?')) return;
    
    api.post(`/milestones/${milestoneId}/review`, { decision, remarks: '' })
      .then(() => {
        // Refresh data
        api.get('/supervisor/students').then(r => setData(r.data));
      })
      .catch(err => alert('Failed to submit review: ' + err.message));
  };

  return (
    <div>
      <div className="hero-banner">
        <div>
          <div className="hero-tag">Supervisor Dashboard</div>
          <h1 className="hero-title">{user?.name}</h1>
          <p className="hero-sub">Manage your students' research progress and review submitted milestones.</p>
        </div>
        <div className="hero-badge" style={{ marginTop: 16 }}>
          <span className="hero-pulse" />
          Supervisor
        </div>
      </div>

      {/* Stats */}
      <div className="stat-grid" style={{ marginBottom: 24 }}>
        <div className="stat-card">
          <div className="stat-icon green">👥</div>
          <div className="stat-number">{students.length}</div>
          <div className="stat-label">Assigned Students</div>
        </div>
        <div className="stat-card">
          <div className="stat-icon amber">⏳</div>
          <div className="stat-number">{pending.length}</div>
          <div className="stat-label">Pending Reviews</div>
        </div>
      </div>

      {/* Pending Reviews */}
      {pending.length > 0 && (
        <div className="card" style={{ marginBottom: 24, borderLeft: '4px solid var(--amber-500)' }}>
          <div className="card-title">⚠️ Milestones Awaiting Your Review</div>
          <div className="tbl-wrap">
            <table>
              <thead><tr><th>Student</th><th>Milestone</th><th>Submitted</th><th></th></tr></thead>
              <tbody>
                {pending.map(m => (
                  <tr key={m.id}>
                    <td style={{ fontWeight: 700 }}>{m.thesis?.student?.user?.name ?? '—'}</td>
                    <td>{m.template?.name}</td>
                    <td style={{ color: 'var(--slate-400)' }}>{m.submitted_at ? new Date(m.submitted_at).toLocaleDateString() : '—'}</td>
                    <td>
                      <div style={{ display: 'flex', gap: '8px' }}>
                        <button onClick={() => handleReview(m.id, 'accepted')} className="btn" style={{ padding: '4px 12px', fontSize: '11px', backgroundColor: 'var(--green-500)', color: 'white' }}>Accept</button>
                        <button onClick={() => handleReview(m.id, 'rejected')} className="btn" style={{ padding: '4px 12px', fontSize: '11px', backgroundColor: '#fee2e2', color: '#b91c1c' }}>Reject</button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Students table */}
      <div className="card">
        <div className="card-title">Supervised Students</div>
        {students.length === 0 ? (
          <div className="empty-state">
            <div className="empty-icon">👥</div>
            <div className="empty-title">No students assigned yet</div>
            <div className="empty-desc">Contact your programme coordinator for student assignments.</div>
          </div>
        ) : (
          <div className="tbl-wrap">
            <table>
              <thead><tr><th>Name</th><th>Thesis Title</th><th>Status</th><th>Progress</th><th>Milestones</th></tr></thead>
              <tbody>
                {students.map(s => (
                  <tr key={s.id}>
                    <td>
                      <div style={{ fontWeight: 700, color: 'var(--slate-900)' }}>{s.user?.name ?? s.name}</div>
                      <div style={{ fontSize: 11, color: 'var(--slate-400)' }}>{s.user?.email}</div>
                    </td>
                    <td style={{ maxWidth: 220, fontSize: 12 }}>{s.thesis_title ?? '—'}</td>
                    <td><span className={`badge ${s.thesis_status === 'active' ? 'green' : 'slate'}`}>{s.thesis_status ?? 'pending'}</span></td>
                    <td>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <div className="progress-wrap" style={{ flex: 1, minWidth: 80 }}>
                          <div className="progress-bar" style={{ width: `${s.overall_progress ?? 0}%` }} />
                        </div>
                        <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--green-600)' }}>{s.overall_progress ?? 0}%</span>
                      </div>
                    </td>
                    <td style={{ fontSize: 12, fontWeight: 700 }}>{s.milestones_done ?? 0}/{s.milestones_total ?? 0}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
