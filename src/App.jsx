import { Route,createBrowserRouter,createRoutesFromElements, RouterProvider  } from "react-router-dom"
import HomePage from "./pages/HomePage"
import JobsPage from "./pages/JobsPage";
import JobPage from "./pages/JobPage";
import EditJobPage from "./pages/EditJobPage";
import NavbarLayout from "./layouts/NavbarLayout"
import NotFound from "./pages/NotFound";
import AddJob from "./pages/AddJob";

  // Add New Job
  const addJobFn = async (newJob) => {
    const res = await fetch('https://aleksa-scandiweb.shop/socialNetwork/jobs.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'API_KEY': import.meta.env.VITE_API_KEY,
      },
      body: JSON.stringify(newJob),
    });
    return;
  };
  
   // Update Job
   const updateJob = async (job) => {
    const res = await fetch(`https://aleksa-scandiweb.shop/socialNetwork/jobs.php/?id=${job.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'API_KEY': import.meta.env.VITE_API_KEY,
      },
      body: JSON.stringify(job),
    });
    if (res.ok) {

      return;

    }
  };

  const deleteJob = async (id) => {

    const res = await fetch(`https://aleksa-scandiweb.shop/socialNetwork/jobs.php/?id=${id}`, {
      method: 'DELETE',
      headers:{
        'API_KEY': import.meta.env.VITE_API_KEY,
      }
    
    });

  }
function App() {
  const router = createBrowserRouter(
  createRoutesFromElements (
  <Route path="/"  element={<NavbarLayout />} >
    <Route path="/"  element={<HomePage />} />
    <Route path="/jobs"  element={<JobsPage />} />
    <Route path="/add-job"  element={<AddJob addJobSubmit={addJobFn} />} />
    <Route path="/job/:id"  element={<JobPage deleteJob={deleteJob} />} />
    <Route
          path='/edit-job/:id'
          element={<EditJobPage updateJobSubmit={updateJob} />}
         
        />
    <Route path="*"  element={<NotFound />} />
  </Route>

)
  );
  return  < RouterProvider router={router} />
  
};

export default App