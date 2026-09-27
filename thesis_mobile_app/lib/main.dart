import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io' show Platform;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:file_picker/file_picker.dart';
import 'package:path_provider/path_provider.dart';

// -----------------------------------------------------------------------------
// App Theme & Constants
// -----------------------------------------------------------------------------
class AppTheme {
  // Core Colors
  static const Color primary = Color(0xFF0F172A);
  static const Color primaryLight = Color(0xFF1E293B);
  static const Color secondary = Color(0xFF10B981);
  static const Color background = Color(0xFFF8FAFC);
  static const Color surface = Colors.white;
  
  // Semantic Colors
  static const Color success = Color(0xFF10B981);
  static const Color warning = Color(0xFFF59E0B);
  static const Color error = Color(0xFFEF4444);
  static const Color info = Color(0xFF3B82F6);
  
  // Text Colors
  static const Color textMain = Color(0xFF0F172A);
  static const Color textMuted = Color(0xFF64748B);
  static const Color textLight = Color(0xFF94A3B8);
  
  // UI Elements
  static const Color border = Color(0xFFF1F5F9);
  
  // Typography
  static TextStyle get heading1 => const TextStyle(fontSize: 32, fontWeight: FontWeight.w900, color: textMain, letterSpacing: -1);
  static TextStyle get heading2 => const TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: textMain, height: 1.2);
  static TextStyle get heading3 => const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: textMain, height: 1.3);
  static TextStyle get body => const TextStyle(fontSize: 14, color: textMuted, height: 1.6);
  static TextStyle get label => const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: textLight, letterSpacing: 1.5);
}

String get kApiBase {
  if (kReleaseMode) {
    return 'https://thesis.acetel.edu.ng'; 
  }
  
  try {
    if (Platform.isAndroid) return 'http://10.0.2.2:8000';
    return 'http://localhost:8000';
  } catch (_) {
    return 'http://localhost:8000';
  }
}
String get kLogoUrl => '$kApiBase/images/acetel-logo.jpeg';

void main() {
  runApp(const ThesisMonitoringApp());
}

class ThesisMonitoringApp extends StatelessWidget {
  const ThesisMonitoringApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Monitorsly',
      theme: ThemeData(
        textTheme: GoogleFonts.outfitTextTheme(),
        scaffoldBackgroundColor: AppTheme.background,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppTheme.secondary,
          primary: AppTheme.primary,
          secondary: AppTheme.secondary,
          surface: AppTheme.surface,
        ),
        useMaterial3: true,
      ),
      home: const AuthCheck(),
      debugShowCheckedModeBanner: false,
    );
  }
}

// -----------------------------------------------------------------------------
// Auth Logic
// -----------------------------------------------------------------------------

class AuthCheck extends StatefulWidget {
  const AuthCheck({super.key});

  @override
  State<AuthCheck> createState() => _AuthCheckState();
}

class _AuthCheckState extends State<AuthCheck> {
  @override
  void initState() {
    super.initState();
    _checkToken();
  }

  Future<void> _checkToken() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    
    if (mounted) {
      if (token != null) {
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            pageBuilder: (c, a1, a2) => const DashboardScreen(),
            transitionsBuilder: (c, a1, a2, child) => FadeTransition(opacity: a1, child: child),
          ),
        );
      } else {
        Navigator.of(context).pushReplacement(
          PageRouteBuilder(
            pageBuilder: (c, a1, a2) => const LoginScreen(),
            transitionsBuilder: (c, a1, a2, child) => FadeTransition(opacity: a1, child: child),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: CircularProgressIndicator(color: AppTheme.secondary, strokeWidth: 5),
      ),
    );
  }
}

// -----------------------------------------------------------------------------
// Components
// -----------------------------------------------------------------------------

class PremiumButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final bool isLoading;

  const PremiumButton({super.key, required this.text, this.onPressed, this.isLoading = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      height: 60,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primary, AppTheme.primaryLight],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primary.withOpacity(0.3),
            blurRadius: 20,
            offset: const Offset(0, 10),
          )
        ],
      ),
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          foregroundColor: Colors.white,
          shadowColor: Colors.transparent,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        ),
        child: isLoading 
          ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
          : Text(text.toUpperCase(), style: const TextStyle(fontWeight: FontWeight.w900, letterSpacing: 1.2, fontSize: 13)),
      ),
    );
  }
}

// -----------------------------------------------------------------------------
// Login Screen
// -----------------------------------------------------------------------------

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _error;

  Future<void> _login() async {
    setState(() { _isLoading = true; _error = null; });
    try {
      final response = await http.post(
        Uri.parse('$kApiBase/api/login'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'email': _emailController.text, 'password': _passwordController.text}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', data['token']);
        if (mounted) Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const DashboardScreen()));
      } else {
        setState(() => _error = jsonDecode(response.body)['message'] ?? 'Access Denied');
      }
    } catch (e) {
      setState(() => _error = 'Institutional bridge failure. Verify server connectivity.');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          Positioned(
            top: -100,
            right: -100,
            child: Container(width: 300, height: 300, decoration: BoxDecoration(color: AppTheme.secondary.withOpacity(0.1), shape: BoxShape.circle)),
          ),
          Positioned(
            bottom: -50,
            left: -50,
            child: Container(width: 200, height: 200, decoration: BoxDecoration(color: AppTheme.secondary.withOpacity(0.05), shape: BoxShape.circle)),
          ),
          SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 40),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 40),
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      color: AppTheme.surface,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [BoxShadow(color: AppTheme.primary.withOpacity(0.08), blurRadius: 40, offset: const Offset(0, 20))],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(24),
                      child: Image.network(
                        kLogoUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (c, e, s) => const Icon(Icons.school, color: AppTheme.secondary, size: 40),
                      ),
                    ),
                  ),
                  const SizedBox(height: 32),
                  Text('Monitorsly', style: AppTheme.heading1.copyWith(fontSize: 42)),
                  Text('University Portfolio Management.', style: AppTheme.body.copyWith(fontWeight: FontWeight.w500)),
                  const SizedBox(height: 56),
                  
                  if (_error != null)
                    Container(
                      padding: const EdgeInsets.all(16),
                      margin: const EdgeInsets.only(bottom: 32),
                      decoration: BoxDecoration(color: const Color(0xFFFEF2F2), borderRadius: BorderRadius.circular(16), border: Border.all(color: const Color(0xFFFEE2E2))),
                      child: Row(
                        children: [
                          const Icon(Icons.error_outline, color: AppTheme.error, size: 20),
                          const SizedBox(width: 12),
                          Expanded(child: Text(_error!, style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 13, fontWeight: FontWeight.w700))),
                        ],
                      ),
                    ),

                  Text('CREDENTIALS', style: AppTheme.label),
                  const SizedBox(height: 12),
                  
                  TextField(
                    controller: _emailController,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.textMain),
                    decoration: _inputStyle('Email Address', Icons.alternate_email),
                  ),
                  const SizedBox(height: 20),
                  TextField(
                    controller: _passwordController,
                    obscureText: true,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.textMain),
                    decoration: _inputStyle('Access Matrix', Icons.lock_outline),
                  ),
                  const SizedBox(height: 40),
                  PremiumButton(text: 'Authenticate', onPressed: _login, isLoading: _isLoading),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _inputStyle(String label, IconData icon) {
    return InputDecoration(
      labelText: label,
      labelStyle: const TextStyle(color: AppTheme.textLight, fontWeight: FontWeight.w600),
      prefixIcon: Icon(icon, color: AppTheme.textLight, size: 20),
      filled: true,
      fillColor: AppTheme.surface,
      contentPadding: const EdgeInsets.symmetric(vertical: 20, horizontal: 20),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: BorderSide.none),
      enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: const BorderSide(color: AppTheme.border)),
      focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(18), borderSide: const BorderSide(color: AppTheme.secondary, width: 2)),
    );
  }
}

// -----------------------------------------------------------------------------
// Dashboard Screen
// -----------------------------------------------------------------------------

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _data;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  Future<void> _fetchData() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    try {
      final response = await http.get(
        Uri.parse('$kApiBase/api/thesis'),
        headers: {'Authorization': 'Bearer $token', 'Accept': 'application/json'},
      );
      if (response.statusCode == 200) {
        setState(() { _data = jsonDecode(response.body); _isLoading = false; });
      } else {
        _logout();
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text("Network error: Unable to reach ACETEL server."), backgroundColor: AppTheme.error));
      }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    if (mounted) Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator(color: AppTheme.secondary)));
    }

    final thesis = _data?['thesis'];
    final List<dynamic> rawMilestones = thesis?['milestones'] ?? [];
    final List<Map<String, dynamic>> milestones = List<Map<String, dynamic>>.from(rawMilestones);
    
    // Sort milestones serially
    milestones.sort((a, b) {
      final int orderA = a['template']?['order'] ?? 999;
      final int orderB = b['template']?['order'] ?? 999;
      return orderA.compareTo(orderB);
    });
    
    // Status Logic
    int daysSinceUpdate = 0;
    if (thesis != null && thesis['updated_at'] != null) {
      final updated = DateTime.parse(thesis['updated_at']);
      daysSinceUpdate = DateTime.now().difference(updated).inDays;
    }
    
    String alertStatus = "ON TRACK";
    Color alertColor = AppTheme.success;
    if (daysSinceUpdate > 30) {
      alertStatus = "STALLED"; alertColor = AppTheme.error;
    } else if (daysSinceUpdate > 14) {
      alertStatus = "DELAYED"; alertColor = AppTheme.warning;
    }

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 180,
            floating: false,
            pinned: true,
            backgroundColor: AppTheme.primary,
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [AppTheme.primary, AppTheme.primaryLight],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Container(
                              width: 44,
                              height: 44,
                              decoration: BoxDecoration(color: AppTheme.surface, borderRadius: BorderRadius.circular(12)),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.network(kLogoUrl, fit: BoxFit.cover, errorBuilder: (c,e,s) => const Icon(Icons.school, size: 20, color: AppTheme.secondary)),
                              ),
                            ),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: alertColor.withOpacity(0.1), 
                                    borderRadius: BorderRadius.circular(10), 
                                    border: Border.all(color: alertColor.withOpacity(0.4))
                                  ),
                                  child: Text(alertStatus, style: TextStyle(color: alertColor, fontWeight: FontWeight.w900, fontSize: 10, letterSpacing: 1.5)),
                                ),
                                const SizedBox(width: 8),
                                IconButton(onPressed: _logout, icon: const Icon(Icons.logout, color: Colors.white54, size: 20)),
                              ],
                            ),
                          ],
                        ),
                        const Spacer(),
                        Text(_data?['user']?['name'] ?? 'Scholar', style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.w900, letterSpacing: -1)),
                        Text(_data?['program']?['name'] ?? 'Research Portal', style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 13, fontWeight: FontWeight.w600)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ),
          
          SliverPadding(
            padding: const EdgeInsets.all(24),
            sliver: SliverList(
              delegate: SliverChildListDelegate([
                Text('THESIS PROJECT', style: AppTheme.label),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: AppTheme.surface, 
                    borderRadius: BorderRadius.circular(28), 
                    border: Border.all(color: AppTheme.border), 
                    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 40, offset: const Offset(0, 10))]
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(thesis?['title'] ?? 'Title TBD', style: AppTheme.heading3),
                      const SizedBox(height: 24),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: LinearProgressIndicator(value: 0.4, minHeight: 8, backgroundColor: AppTheme.border, valueColor: AlwaysStoppedAnimation<Color>(alertColor)),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Current Progress', style: AppTheme.body.copyWith(fontWeight: FontWeight.w700, fontSize: 12)),
                          Text('40%', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: alertColor)),
                        ],
                      ),
                    ],
                  ),
                ),
                
                const SizedBox(height: 48),
                Text('RESEARCH PROTOCOL', style: AppTheme.label),
                const SizedBox(height: 12),
                ...milestones.map((m) => _buildMilestoneNode(m)).toList(),
                if (milestones.isEmpty)
                   Container(
                     padding: const EdgeInsets.all(40),
                     decoration: BoxDecoration(color: AppTheme.border.withOpacity(0.5), borderRadius: BorderRadius.circular(24)),
                     child: Center(child: Text('Protocol synchronization pending...', style: AppTheme.body.copyWith(fontWeight: FontWeight.w700, fontSize: 13))),
                   ),
              ]),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMilestoneNode(Map<String, dynamic> milestone) {
    final template = milestone['template'];
    final String status = (milestone['status'] ?? 'not_started').toString().toLowerCase();
    
    Color nodeColor;
    IconData nodeIcon;
    String statusLabel = status.replaceAll('_', ' ').toUpperCase();

    switch (status) {
      case 'approved':
      case 'completed':
      case 'cleared':
        nodeColor = AppTheme.success;
        nodeIcon = Icons.check_circle;
        break;
      case 'submitted':
        nodeColor = AppTheme.info;
        nodeIcon = Icons.pending_actions;
        break;
      case 'revision_required':
        nodeColor = AppTheme.error;
        nodeIcon = Icons.assignment_late;
        break;
      case 'partially_approved':
        nodeColor = AppTheme.warning;
        nodeIcon = Icons.published_with_changes;
        break;
      default:
        nodeColor = AppTheme.textLight;
        nodeIcon = Icons.circle_outlined;
        statusLabel = "NOT STARTED";
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: nodeColor.withOpacity(0.05),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: nodeColor.withOpacity(0.2)),
      ),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => MilestoneDetailScreen(milestone: milestone),
            ),
          );
        },
        child: Row(
          children: [
            Icon(nodeIcon, color: nodeColor, size: 24),
            const SizedBox(width: 20),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('PHASE ${template?['order'] ?? "?"}', style: AppTheme.label.copyWith(color: nodeColor)),
                  Text(template?['name'] ?? 'Unknown Milestone', style: AppTheme.heading3.copyWith(fontSize: 15)),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(color: nodeColor.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
              child: Text(statusLabel, style: TextStyle(color: nodeColor, fontWeight: FontWeight.bold, fontSize: 9)),
            ),
          ],
        ),
      ),
    );
  }
}

// -----------------------------------------------------------------------------
// Milestone Detail Screen
// -----------------------------------------------------------------------------

class MilestoneDetailScreen extends StatefulWidget {
  final Map<String, dynamic> milestone;

  const MilestoneDetailScreen({super.key, required this.milestone});

  @override
  State<MilestoneDetailScreen> createState() => _MilestoneDetailScreenState();
}

class _MilestoneDetailScreenState extends State<MilestoneDetailScreen> {
  final TextEditingController _msgController = TextEditingController();
  bool _isUploading = false;
  bool _isSending = false;

  Future<void> _uploadArtifact() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'png'],
    );
    if (result != null) {
      setState(() => _isUploading = true);
      try {
        final prefs = await SharedPreferences.getInstance();
        final token = prefs.getString('auth_token');
        final uri = Uri.parse('$kApiBase/api/milestones/${widget.milestone['id']}/submit');
        
        var request = http.MultipartRequest('POST', uri);
        request.headers['Authorization'] = 'Bearer $token';
        request.headers['Accept'] = 'application/json';
        request.fields['description'] = 'Artifact submitted via Mobile Portal';
        
        var file = await http.MultipartFile.fromPath('file', result.files.single.path!);
        request.files.add(file);
        
        var response = await request.send();
        if (response.statusCode == 200 || response.statusCode == 201) {
          if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Document successfully uploaded to the institutional repository.'), backgroundColor: AppTheme.success));
        } else {
          throw Exception('Upload Failed');
        }
      } catch (e) {
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Failed to bridge document. Check network connection.'), backgroundColor: AppTheme.error));
      } finally {
        if (mounted) setState(() => _isUploading = false);
      }
    }
  }

  Future<void> _sendMessage() async {
    if (_msgController.text.trim().isEmpty) return;
    setState(() => _isSending = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');
      
      final response = await http.post(
        Uri.parse('$kApiBase/api/messages'),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: jsonEncode({
          'thesis_project_id': widget.milestone['thesis_project_id'],
          'student_milestone_id': widget.milestone['id'],
          'content': _msgController.text
        }),
      );
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        _msgController.clear();
        if (mounted) FocusScope.of(context).unfocus();
        if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Message delivered to stakeholders.'), backgroundColor: AppTheme.primary));
      } else {
        throw Exception();
      }
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Communication link disrupted.'), backgroundColor: AppTheme.error));
    } finally {
      if (mounted) setState(() => _isSending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final template = widget.milestone['template'];
    final String status = (widget.milestone['status'] ?? 'not_started').toString().toLowerCase();

    Color statusColor;
    String statusLabel = status.replaceAll('_', ' ').toUpperCase();

    switch (status) {
      case 'approved':
      case 'completed':
      case 'cleared':
        statusColor = AppTheme.success;
        break;
      case 'submitted':
        statusColor = AppTheme.info;
        break;
      case 'revision_required':
        statusColor = AppTheme.error;
        break;
      case 'partially_approved':
        statusColor = AppTheme.warning;
        break;
      default:
        statusColor = AppTheme.textLight;
        statusLabel = "NOT STARTED";
    }

    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppTheme.primary,
        foregroundColor: Colors.white,
        title: const Text('Milestone Details', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(color: AppTheme.primaryLight, borderRadius: BorderRadius.circular(10)),
              child: Text('PHASE ${template?['order'] ?? "?"}', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 12, letterSpacing: 1.5)),
            ),
            const SizedBox(height: 16),
            Text(template?['name'] ?? 'Unknown', style: AppTheme.heading2),
            const SizedBox(height: 24),
            
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.surface,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppTheme.border),
                boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 20, offset: const Offset(0, 5))],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('STATUS', style: AppTheme.label),
                  const SizedBox(height: 8),
                  Text(statusLabel, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: statusColor)),
                  const Divider(height: 32, color: AppTheme.border),
                  Text('DESCRIPTION', style: AppTheme.label),
                  const SizedBox(height: 8),
                  Text(template?['description'] ?? 'No description provided.', style: AppTheme.body),
                ],
              ),
            ),
            
            const SizedBox(height: 32),
            if (status != 'approved' && status != 'completed')
              PremiumButton(
                text: 'Upload Artifact Document',
                onPressed: _uploadArtifact,
                isLoading: _isUploading,
              ),
              
            const SizedBox(height: 48),
            Text('COMMUNICATION CHANNEL', style: AppTheme.label),
            const SizedBox(height: 16),
            TextField(
              controller: _msgController,
              maxLines: 3,
              style: AppTheme.body.copyWith(color: AppTheme.textMain),
              decoration: InputDecoration(
                hintText: 'Type your message to supervisors and coordinator...',
                hintStyle: const TextStyle(color: AppTheme.textLight),
                filled: true,
                fillColor: AppTheme.surface,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
                enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: AppTheme.border)),
                focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: const BorderSide(color: AppTheme.secondary, width: 2)),
              ),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _isSending ? null : _sendMessage,
              icon: _isSending ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Icon(Icons.send, size: 16),
              label: Text(_isSending ? 'Transmitting...' : 'Send Message'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
                foregroundColor: Colors.white,
                minimumSize: const Size(double.infinity, 50),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
      ),
    );
  }
}

