<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fuel Variance Approval Required</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #dc3545, #ffc107); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .alert-badge { background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; display: inline-block; margin-top: 10px; }
        .content { padding: 30px 20px; }
        .variance-card { background: #f8f9fa; border-left: 5px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 0 5px 5px 0; }
        .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; color: #6c757d; }
        .detail-value { color: #495057; }
        .risk-critical { color: #dc3545; font-weight: bold; }
        .risk-high { color: #fd7e14; font-weight: bold; }
        .risk-medium { color: #ffc107; font-weight: bold; }
        .approve-btn { display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 15px 40px; text-decoration: none; border-radius: 25px; font-weight: bold; font-size: 16px; margin: 20px 0; text-align: center; box-shadow: 0 4px 8px rgba(40,167,69,0.3); }
        .approve-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(40,167,69,0.4); }
        .footer { background: #343a40; color: #adb5bd; padding: 20px; text-align: center; font-size: 12px; }
        .financial-impact { background: linear-gradient(135deg, #17a2b8, #6f42c1); color: white; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0; }
        @media (max-width: 600px) { .detail-row { flex-direction: column; } .detail-label { margin-bottom: 5px; } }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚨 FUEL VARIANCE DETECTED</h1>
            <div class="alert-badge">IMMEDIATE APPROVAL REQUIRED</div>
        </div>

        <!-- Content -->
        <div class="content">
            <p><strong>Hello {{ $recipientName }},</strong></p>
            
            <p>A significant fuel variance has been detected during reconciliation and requires your immediate approval.</p>

            <!-- Variance Summary Card -->
            <div class="variance-card">
                <h3 style="margin-top: 0; color: #dc3545;">⚠️ Variance Summary</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Station:</span>
                    <span class="detail-value">{{ $varianceData['station_name'] ?? 'Unknown Station' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Tank Number:</span>
                    <span class="detail-value">Tank #{{ $varianceData['tank_number'] ?? 'N/A' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Variance Percentage:</span>
                    <span class="detail-value risk-{{ strtolower($varianceData['risk_level'] ?? 'medium') }}">
                        {{ number_format(abs($varianceData['variance_percentage'] ?? 0), 2) }}%
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Volume Impact:</span>
                    <span class="detail-value">{{ number_format(abs($varianceData['variance_liters'] ?? 0), 0) }} Liters</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Risk Level:</span>
                    <span class="detail-value risk-{{ strtolower($varianceData['risk_level'] ?? 'medium') }}">
                        {{ $varianceData['risk_level'] ?? 'Unknown' }}
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Detection Time:</span>
                    <span class="detail-value">{{ now()->format('Y-m-d H:i:s T') }}</span>
                </div>
            </div>

            <!-- Financial Impact -->
            <div class="financial-impact">
                <h4 style="margin: 0 0 10px 0;">💰 Financial Impact</h4>
                <div style="font-size: 24px; font-weight: bold;">
                    UGX {{ number_format(abs($varianceData['financial_impact'] ?? 0), 0) }}
                </div>
            </div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $approvalUrl }}" class="approve-btn">
                    🔗 REVIEW & APPROVE VARIANCE
                </a>
            </div>

            <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong>⏰ Action Required:</strong> This approval link expires in 24 hours for security purposes. Please review and take action promptly.
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li>Click the approval link above</li>
                <li>Log into the FUEL_ERP system</li>
                <li>Review variance details and investigation notes</li>
                <li>Approve or reject with comments</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>FUEL_ERP Automated Notification System</strong></p>
            <p>Protecting fuel station operations with real-time variance detection</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
