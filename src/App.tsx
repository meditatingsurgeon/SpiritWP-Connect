/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

export default function App() {
  return (
    <div className="min-h-screen bg-[#0D0D1A] text-white font-sans flex items-center justify-center p-4 overflow-hidden relative">
      {/* Background decorative elements */}
      <div className="absolute top-0 right-0 w-96 h-96 bg-[#0896F2]/10 rounded-full blur-[100px] transform translate-x-1/3 -translate-y-1/3 pointer-events-none" />
      <div className="absolute bottom-0 left-0 w-96 h-96 bg-[#FF0060]/10 rounded-full blur-[100px] transform -translate-x-1/3 translate-y-1/3 pointer-events-none" />

      <div className="max-w-3xl w-full bg-[#1A1A2E] rounded-xl border border-[#0896F2]/20 p-8 md:p-10 shadow-2xl relative overflow-hidden z-10">
        <div className="flex flex-col sm:flex-row sm:items-center gap-5 border-b border-[#0896F2]/20 pb-6 mb-8">
          <div className="w-12 h-12 bg-[#FF0060] flex-shrink-0 rounded-sm flex items-center justify-center transform -rotate-12 shadow-[0_0_15px_rgba(255,0,96,0.3)]">
            <span className="font-black text-2xl italic text-white leading-none mt-1">S</span>
          </div>
          <div>
            <h1 className="text-3xl font-black tracking-tighter uppercase leading-none">
              SpiritWP <span className="text-[#0896F2]">//</span> Connect
            </h1>
            <p className="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-bold mt-2">
              Production-Grade Sync Engine
            </p>
          </div>
        </div>
        
        <p className="text-gray-300 mb-8 text-lg leading-relaxed font-mono text-sm sm:text-base">
          The WordPress plugin source code is being generated in the <code className="text-[#FF0060] bg-[#0D0D1A] border border-[#FF0060]/30 px-2 py-0.5 rounded italic">spiritwp-connect</code> folder. 
          As an AI agent, I am building the requested PHP files, which you can download as a ZIP file when complete.
        </p>

        <div className="bg-[#0D0D1A]/50 rounded-lg p-6 border-l-4 border-[#0896F2] relative overflow-hidden">
          <span className="absolute top-[-15px] right-[-10px] text-8xl font-black text-white/5 italic pointer-events-none select-none">01</span>
          
          <h3 className="text-xs uppercase font-black text-gray-400 mb-4 tracking-widest flex items-center gap-2">
            <div className="w-2 h-2 rounded-full bg-[#00FF00] shadow-[0_0_10px_#00FF00]"></div>
            Next Steps / Directives
          </h3>
          
          <ul className="list-none ml-0 mt-2 space-y-0 font-mono text-xs sm:text-sm text-gray-300 relative z-10">
            <li className="flex items-start gap-3 border-b border-[#0896F2]/5 py-3 hover:bg-[#0896F2]/5 transition-colors px-2 rounded-sm">
              <span className="text-[#0896F2] font-black mt-0.5">_</span>
              <span>Wait for the code generation to complete</span>
            </li>
            <li className="flex items-start gap-3 border-b border-[#0896F2]/5 py-3 hover:bg-[#0896F2]/5 transition-colors px-2 rounded-sm">
              <span className="text-[#0896F2] font-black mt-0.5">_</span>
              <span>Click the "Share" or "Export" option in AI Studio to download the ZIP</span>
            </li>
            <li className="flex items-start gap-3 border-b border-[#0896F2]/5 py-3 hover:bg-[#0896F2]/5 transition-colors px-2 rounded-sm">
              <span className="text-[#0896F2] font-black mt-0.5">_</span>
              <span>Upload the plugin to your WordPress site under Plugins &gt; Add New</span>
            </li>
            <li className="flex items-start gap-3 border-b border-[#0896F2]/5 py-3 hover:bg-[#0896F2]/5 transition-colors px-2 rounded-sm">
              <span className="text-[#0896F2] font-black mt-0.5">_</span>
              <span>Run <code className="text-[#FF0060]">composer install</code> inside the plugin folder if not pre-packaged</span>
            </li>
            <li className="flex items-start gap-3 py-3 hover:bg-[#0896F2]/5 transition-colors px-2 rounded-sm">
              <span className="text-[#0896F2] font-black mt-0.5">_</span>
              <span>Navigate to the SpiritWP Connect settings page in your WP Admin</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  );
}
