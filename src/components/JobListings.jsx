import { useState, useEffect } from 'react'
import JobListing from './JobListing'
function jobListings({isHome = false}) {

  const [jobs, setJobs] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
   const fetchJobs = async () =>{
    const api_url = isHome ? '/api/jobs?_limit=3' : '/api/jobs'
    try {
      const result = await fetch(api_url);
      const data  = await result.json()
      setJobs(data)
      setLoading(true)
      
    } catch (error) {
      console.log('Error catching data', error);
    }finally{
      setLoading(false)
    }
   }  
   fetchJobs()
  }, [])
  
  return (
    <section className="bg-blue-50 px-4 py-10">
      <div className="container-xl lg:container m-auto">
        <h2 className="text-3xl font-bold text-indigo-500 mb-6 text-center">
         {isHome ? 'Recent Jobs' : 'Browse Jobs'} 
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {loading ? (<h2 className='text-red-500 font-bold text-2xl'>Loading...</h2>) : (
          <>
          {jobs.map((job)=>(
          < JobListing  key={job.id}  job={job}/>
          ))}
          
          </>
          )}
        </div>
      </div>
    </section>
  )
}

export default jobListings